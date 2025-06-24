@extends('layouts.app')
@section('content')
<main class="pt-95">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container" style="margin-top: 100px;">
    <h2 class="page-title">Shipping and Checkout</h2>
    <div class="checkout-steps">
        <a href="{{route('cart.index')}}" class="checkout-steps__item active">
            <span class="checkout-steps__item-number">01</span>
            <span class="checkout-steps__item-title">
                <span>Shopping Bag</span>
                <em>Manage Your Items List</em>
            </span>
        </a>

            <a href="javascript:void(0)" class="checkout-steps__item active">
                <span class="checkout-steps__item-number">02</span>
                <span class="checkout-steps__item-title">
                    <span>Shipping and Checkout</span>
                    <em>Checkout Your Items List</em>
                </span>
            </a>
            <a href="{{route('cart.order.confirmation')}}" class="checkout-steps__item">
                <span class="checkout-steps__item-number">03</span>
                <span class="checkout-steps__item-title">
                    <span>Confirmation</span>
                    <em>Review And Submit Your Order</em>
                </span>
            </a>
        </div>

        <form action="{{ route('cart.place.an.order') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h2>Pilih Alamat Pengiriman</h2>
            <select name="selected_address_id" class="form-select">
                <option value="">-- Pilih Alamat Tersimpan --</option>
                @foreach ($addresses as $address)
                    <option value="{{ $address->id }}" {{ old('selected_address_id') == $address->id ? 'selected' : '' }}>
                        {{ $address->name }} - {{ $address->phone }} - {{ $address->address }}, {{ $address->city }}
                    </option>
                @endforeach
            </select>

            <div class="checkout-form">
                <div class="billing-info__wrapper">
                    <h3>Atau Tambahkan Alamat Baru</h3>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="name" value="{{old('name')}}" placeholder="Nama Lengkap" >
                                <label for="name">Full Name *</label>
                                @error('name') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="phone" value="{{old('phone')}}" placeholder="Nomor Telepon" >
                                <label for="phone">Phone Number *</label>
                                @error('phone') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="zip" value="{{old('zip')}}" placeholder="Kode Pos" >
                                <label for="zip">Pincode *</label>
                                @error('zip') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="state" value="{{old('state')}}" placeholder="Provinsi" >
                                <label for="state">State *</label>
                                @error('state') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="city" value="{{old('city')}}" placeholder="Kota" >
                                <label for="city">Town / City *</label>
                                @error('city') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="address" value="{{old('address')}}" placeholder="Jl. Melati No. 123" >
                                <label for="address">House no, Building Name *</label>
                                @error('address') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="locality" value="{{old('locality')}}" placeholder="Area / Perumahan" >
                                <label for="locality">Road Name, Area, Colony *</label>
                                @error('locality') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating my-3">
                                <input type="text" class="form-control" name="landmark" value="{{old('landmark')}}" placeholder="Dekat Masjid/Alfamart" >
                                <label for="landmark">Landmark *</label>
                                @error('landmark') <span class="text-danger">{{$message}}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout__totals-wrapper mt-5">
                    <div class="checkout__totals">
                        <h3>Your Order</h3>
                        <table class="checkout-cart-items">
                            <thead>
                                <tr>
                                    <th>PRODUCT</th>
                                    <th align="right">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (Cart::instance('cart')->content() as $item)
                                <tr>
                                    <td>{{$item->name}} x {{$item->qty}}</td>
                                    <td align="right">Rp{{$item->subtotal()}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(Session::has('discounts'))
                            <table class="checkout-totals">
                                <tbody>
                                    <tr><th>Subtotal</th><td class="text-right">Rp{{Cart::instance('cart')->subTotal()}}</td></tr>
                                    <tr><th>Discount ({{Session::get('coupon')['code']}})</th><td class="text-right">Rp{{Session::get('discounts')['discount']}}</td></tr>
                                    <tr><th>Subtotal After Discount</th><td class="text-right">Rp{{Session::get('discounts')['subtotal']}}</td></tr>
                                    <tr><th>Shipping</th><td class="text-right">Free</td></tr>
                                    <tr><th>Tax</th><td class="text-right">Rp{{Session::get('discounts')['tax']}}</td></tr>
                                    <tr><th>Total</th><td class="text-right">Rp{{Session::get('discounts')['total']}}</td></tr>
                                </tbody>
                            </table>
                        @else
                            <table class="checkout-totals">
                                <tbody>
                                    <tr><th>Subtotal</th><td class="text-right">Rp{{Cart::instance('cart')->subtotal()}}</td></tr>
                                    <tr><th>Shipping</th><td class="text-right">Free shipping</td></tr>
                                    <tr><th>VAT</th><td class="text-right">Rp{{Cart::instance('cart')->tax()}}</td></tr>
                                    <tr><th>Total</th><td class="text-right">Rp{{Cart::instance('cart')->total()}}</td></tr>
                                </tbody>
                            </table>
                        @endif
                    </div>

                    <div class="checkout__payment-methods mt-4">
                        <h4>Select Payment Method</h4>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" value="card" id="mode1" required>
                            <label class="form-check-label" for="mode1">Mobile Banking</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" value="cod" id="mode3" required>
                            <label class="form-check-label" for="mode3">Cash on Delivery</label>
                        </div>
                        <div class="policy-text mt-2">
                            Your personal data will be used to process your order, support your experience throughout this
                            website, and for other purposes described in our <a href="terms.html" target="_blank">privacy policy</a>.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-checkout mt-4">PLACE ORDER</button>
                </div>
            </div>
        </form>

    </section>
</main>
@endsection