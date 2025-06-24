@extends('layouts.app')
@section('content')
<main class="pt-90">
  <div class="mb-4 pb-4"></div>
  <section class="shop-checkout container">
        <h2 class="page-title">DATA ORDER</h2>
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
<<<<<<< HEAD
            <a href="javascript:void(0)" class="checkout-steps__item">
=======
            <a href="{{route('cart.order.confirmation')}}" class="checkout-steps__item">
>>>>>>> 3a36b93 (ganti)
                <span class="checkout-steps__item-number">03</span>
                <span class="checkout-steps__item-title">
                    <span>Confirmation</span>
                    <em>Review And Submit Your Order</em>
                </span>
            </a>
        </div>
      <div class="table-responsive"></div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">Order Number</th>
              <th scope="col">Nama Pembeli</th>
              <th scope="col">Alamat</th>
              <th scope="col">No Telepon</th>
              <th scope="col">Date</th>
              <th scope="col">Total</th>
              <th scope="col">Metode Pembayaran</th>
              <th scope="col">Status Pembayaran</th>
              <th scope="col">Status Pesanan</th>
              <th scope="col">Keterangan</th>
            </tr>
          </thead>
            <tbody>
              @forelse($orders as $order)
                <tr>
                  <td>{{$order->id}}</td>
                  <td>{{ $order->name }}</td>
                  <td>{{ $order->address }}</td>
                  <td>{{ $order->phone }}</td>
                  <td>{{ $order->created_at->format('d-m-Y') }}</td>
                  <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                  <td class="text-center">{{ $order->transaction?->mode ?? '-' }}</td>
                  <td class="text-center">
                    @php
                      $status = $order->transaction?->payment_status;
                    @endphp
                    <span class="badge rounded-pill" style="
                      background-color: {{ $status == 'paid' ? '#198754' : ($status == 'pending' ? '#ffc107' : '#dee2e6') }};
                      color: {{ $status == 'paid' ? '#fff' : '#000' }};
                    ">
                      {{ ucfirst($status ?? 'Belum') }}
                    </span>
                  </td>
                  <td class="text-center">
                <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">BAYAR</a>
              </td>
            </tr>
          @empty
            <tr>
              <th scope="col">Order Number</th>
              <th scope="col">Nama Pembeli</th>
              <th scope="col">Alamat</th>
              <th scope="col">No Telepon</th>
              <th scope="col">Date</th>
              <th scope="col">Total</th>
              <th scope="col">Metode Pembayaran</th>
              <th scope="col">Status Pembayaran</th>
              <th scope="col">Status Pesanan</th>
              <th scope="col">Keterangan</th>
            </tr>
<<<<<<< HEAD
          </thead>
            <tbody>
              @forelse($orders as $order)
                <tr>
                    <td>{{$order->id}}</td>
                  <td>{{ $order->name }}</td>
                  <td>{{ $order->address }}</td>
                  <td>{{ $order->phone }}</td>
                  <td>{{ $order->created_at->format('d-m-Y') }}</td>
                  <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                  <td>{{ $order->transaction?->mode ?? '-' }}</td>
                  <td>{{ $order->transaction?->payment_status ?? '-' }}</td>
                  <td>{{ $order->status }}</td>
                  <td>
                      <a href="{{route('customer.orders.show', $order->id)}}" class="btn btn-primary">Detail</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5">Tidak ada data</td>
                </tr>
              @endforelse
=======
          @endforelse
>>>>>>> 3a36b93 (ganti)
            </tbody>
        </table>
      </div>
      <div class="mt-4">
           {{ $orders->links() }}
      </div>
  </section>
</main>
@endsection