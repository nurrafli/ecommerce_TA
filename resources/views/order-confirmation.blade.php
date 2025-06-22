@extends('layouts.app')
@section('content')
<main class="pt-100 bg-soft-blue min-vh-100">
   <section class="shop-checkout container">
      <h2 class="page-title">Cart</h2>
      <div class="checkout-steps">
        <a href="{{route('cart.index')}}" class="checkout-steps__item active">
          <span class="checkout-steps__item-number">01</span>
          <span class="checkout-steps__item-title">
            <span>Shopping Bag</span>
            <em>Manage Your Items List</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item">
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

    {{-- Table --}}
    <div class="table-responsive rounded shadow-sm">
      <table class="table table-bordered table-hover align-middle bg-white">
        <thead class="table-light text-center text-secondary">
          <tr>
            <th>Order #</th>
            <th>Nama Pembeli</th>
            <th>Alamat</th>
            <th>Telepon</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Pembayaran</th>
            <th>Status Bayar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr>
              <td class="text-center">#{{ $order->id }}</td>
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
              <td colspan="10" class="text-center text-muted">Tidak ada data order.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
      {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
  </section>
</main>
@endsection
