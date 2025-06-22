@extends('layouts.app')
@section('content')
<style>
    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #ccc;
        padding-bottom: 0.5rem;
        color: #333;
    }

    .table th {
        background-color: #6a6e51;
        color: white;
        font-size: 13px;
        padding: 10px;
        text-align: center;
    }

    .table td {
        font-size: 13px;
        padding: 10px;
        text-align: center;
        border: 1px solid #6a6e51;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .bg-success {
        background-color: #40c710 !important;
    }

    .bg-danger {
        background-color: #f44032 !important;
    }

    .bg-warning {
        background-color: #f5d700 !important;
        color: #000;
    }

    .order-table-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
</style>

<main class="pt-100">
    <section class="my-account container">
        <h2 class="page-title">Orders</h2>
        <div class="row">
            <div class="col-lg-2">
                @include('user.account-nav')
            </div>

            <div class="col-lg-10">
                <div class="order-table-container">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 80px">Order No</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Subtotal</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th>Items</th>
                                    <th>Delivered On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ $order->phone }}</td>
                                    <td>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format($order->tax, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($order->status == 'delivered')
                                            <span class="badge bg-success">Delivered</span>
                                        @elseif($order->status == 'canceled')
                                            <span class="badge bg-danger">Canceled</span>
                                        @else
                                            <span class="badge bg-warning">Ordered</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td>{{ $order->delivered_date ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('user.order.details', ['order_id' => $order->id]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>                
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
