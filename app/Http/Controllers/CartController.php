<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;


class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart',compact('items'));
    }

    public function add(Request $request)
    {
        Cart::instance('cart')->add($request->id,$request->name,$request->quantity,$request->price)->associate('App\Models\Product');
        return redirect()->back();
    }

    public function increase_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId,$qty);
        return redirect()->back();
    }

    public function remove_item($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    public function empty_cart()
    {
        \Cart::instance('cart')->destroy(); 
        return redirect()->back()->with('success', 'Cart cleared.');
    }   

    public function apply_coupon_code(Request $request)
    {
    $coupon_code = $request->coupon_code;

    if (isset($coupon_code)) {
        // Ambil subtotal dan ubah ke float (hilangkan tanda koma)
        $cartSubtotalRaw = Cart::instance('cart')->subtotal(); // contoh: "900,000.00"
        $cartSubtotal = floatval(str_replace(',', '', $cartSubtotalRaw)); // jadi 900000.00

        // Cari kupon berdasarkan kondisi
        $coupon = Coupon::where('code', $coupon_code)
            ->where('expiry_date', '>=', Carbon::today())
            ->where('cart_value', '<=', $cartSubtotal)
            ->first();

        if (!$coupon) {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        } else {
            // Simpan kupon ke session, pastikan value numerik
            Session::put('coupon', [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => floatval($coupon->value), // konversi ke float untuk mencegah error
                'cart_value' => floatval($coupon->cart_value)
            ]);

            // Tetap panggil fungsi calculateDiscount seperti yang kamu minta
            $this->calculateDiscount();

            return redirect()->back()->with('success', 'Coupon has been applied!');
        }
    } else {
        return redirect()->back()->with('error', 'Invalid coupon code!');
    }
    }

    public function calculateDiscount()
    {
        $discount = 0;

        if (Session::has('coupon')) {
            // Ambil subtotal cart, bersihkan dari koma, ubah ke float
            $cartSubtotalRaw = Cart::instance('cart')->subtotal(); // contoh: "1,200.00"
            $cartSubtotal = floatval(str_replace(',', '', $cartSubtotalRaw)); // hasil: 1200.00

            // Hitung diskon sesuai tipe kupon
            if (Session::get('coupon')['type'] == 'fixed') {
                $discount = floatval(Session::get('coupon')['value']); // pastikan nilai diskon numerik
            } else {
                $discount = ($cartSubtotal * floatval(Session::get('coupon')['value'])) / 100;
            }

            // Hitung total setelah diskon dan pajak
            $subtotalAfterDiscount = $cartSubtotal - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            // Simpan ke session
            Session::put('discounts', [
                'discount' => number_format(floatval($discount), 2, '.', ''),
                'subtotal' => number_format(floatval($subtotalAfterDiscount), 2, '.', ''),
                'tax' => number_format(floatval($taxAfterDiscount), 2, '.', ''),
                'total' => number_format(floatval($totalAfterDiscount), 2, '.', '')
                ]);
        }
    }


    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return back()->with('success','Coupon has been removed!');
    }

    public function checkout()
    {
        if(!Auth::check())
        {
            return redirect()->route('login');
        }

        $addresses = Address::where('user_id',Auth::user()->id)->get();
        return view('checkout', compact('addresses'));
    }

    public function place_an_order(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:cod,card,ewallet',
            'selected_address_id' => 'nullable|exists:addresses,id',
        ]);

        $user = Auth::user();
        $user_id = $user->id;

        // Jika pilih alamat lama
        if ($request->filled('selected_address_id')) {
            $address = Address::where('user_id', $user_id)
                            ->where('id', $request->selected_address_id)
                            ->first();
        } else {
            // Jika tidak pilih alamat lama, validasi dan simpan alamat baru
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits_between:10,13',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);

            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = 'Indonesia';
            $address->user_id = $user_id;
            $address->isdefault = true;
            $address->save();
        }

        // Proses order
        $this->setAmountforCheckout();
        if (!Session::has('checkout')) {
            return redirect()->route('cart.index')->with('error', 'Checkout session tidak ditemukan. Silakan ulangi proses pembelian.');
        }

        $checkout = Session::get('checkout');

        $order = new Order();
        $order->user_id = $user_id;
        $order->order_id = 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)); // kode unik
        $order->subtotal = str_replace(',', '', $checkout['subtotal']);
        $order->discount = str_replace(',', '', $checkout['discount']);
        $order->tax = str_replace(',', '', $checkout['tax']);
        $order->total = str_replace(',', '', $checkout['total']);


        // Isi alamat dari data address
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->save();

        // Simpan order items
        foreach (Cart::instance('cart')->content() as $item) {
            $items = new OrderItem();
            $items->product_id = $item->id;
            $items->order_id = $order->id;
            $items->price = $item->price;
            $items->quantity = $item->qty;
            $items->product_name = $item->name;
            $items->save();
        }

        // Simpan transaksi
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->order_id = $order->id;
        $transaction->mode = $request->mode;
        $transaction->save();

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);

        return redirect()->route('customer.orders.show', ['order' => $order->id]);
    }

    public function order_confirmation()
    {
        $orders = Order::with('transaction')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);;

        return view('order-confirmation', compact('orders'));

    }

    public function show(MidtransService $midtransService, Order $order)
    {
        // get last payment
        $payment = $order->payments->last();
 
        if ($payment == null || $payment->status == 'EXPIRED') {
            $snapToken = $midtransService->createSnapToken($order);
 
            $order->payments()->create([
                'snap_token' => $snapToken,
                'status' => 'PENDING',
            ]);
        } else {
            $snapToken = $payment->snap_token;
        }
 
        return view('show', compact('order', 'snapToken'));
    }

    public function setAmountforCheckout()
    {
        if (Cart::instance('cart')->content()->count() <= 0) {
            Session::forget('checkout');
            return;
        }

        if (Session::has('coupon') && Session::has('discounts')) {
            Session::put('checkout', [
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'tax' => Session::get('discounts')['tax'],
                'total' => Session::get('discounts')['total'],
            ]);
        } else {
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => Cart::instance('cart')->subtotal(),
                'tax' => Cart::instance('cart')->tax(),
                'total' => Cart::instance('cart')->total(),
            ]);
        }
    }

}
