<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Address;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate(10);
        return view('user.orders',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::where('user_id',Auth::user()->id)->where('id',$order_id)->first();
        if($order)
        {
            $items = OrderItem::where('order_id',$order->id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id',$order->id)->first();
            return view('user.order-details',compact('order','items','transaction'));
        }
        else
        {
            return redirect()->route('login');
        }
    }

    public function order_cancel(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = "canceled";
        $order->canceled_date = Carbon::now();
        $order->save();
        return back()->with('status',"Order has been canceled succesfully!");
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|numeric|digits_between:10,13',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

        public function addresses()
    {
        $addresses = Address::where('user_id', Auth::id())->get();
        return view('user.addresses.index', compact('addresses'));
    }

    public function editAddress($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        return view('user.addresses.edit', compact('address'));
    }

    public function updateAddress(Request $request, Address $address)
    {
        // Pastikan user adalah pemilik address
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits_between:10,13',
            'zip' => 'required|numeric|digits:6',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'country' => 'required',
        ]);

        // Jika isdefault dicentang
        $isDefault = $request->has('isdefault') ? true : false;
        if ($isDefault) {
            Address::where('user_id', Auth::id())->update(['isdefault' => false]);
        }

        $address->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'zip' => $request->zip,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,
            'locality' => $request->locality,
            'landmark' => $request->landmark,
            'country' => $request->country,
            'isdefault' => $isDefault,
        ]);

        return redirect()->route('user.addresses')->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Address $address)
    {
        // Pastikan hanya user pemilik yang bisa hapus
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $address->delete();
        return redirect()->route('user.addresses')->with('success', 'Address deleted successfully.');
    }


}
