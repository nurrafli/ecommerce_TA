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

    .card-custom {
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        padding: 1rem;
        background: #fff;
        margin-bottom: 1.5rem;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .table th {
        background-color: #6a6e51;
        color: white;
        font-size: 13px;
        border: 1px solid #dee2e6;
    }

    .table td {
        font-size: 13px;
        border: 1px solid #dee2e6;
    }

    .image img {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 8px;
    }

    .order-detail-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<main class="pt-100">
    <section class="my-account container">
        <h2 class="page-title">Order Details</h2>
        <div class="row">
            <div class="col-lg-2">
                @include('user.account-nav')
            </div>

            <div class="col-lg-10">
                <div class="card-custom">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Order Summary</h5>
                        <a class="btn btn-sm btn-secondary" href="{{route('user.orders')}}">Back</a>
                    </div>
                    <table class="table">
                        <tr>
                            <th>Order No</th><td>{{$order->id}}</td>
                            <th>Phone</th><td>{{$order->phone}}</td>
                        </tr>
                        <tr>
                            <th>Order Date</th><td>{{$order->created_at}}</td>
                            <th>Status</th>
                            <td>
                                @if($order->status == 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($order->status == 'canceled')
                                    <span class="badge bg-danger">Canceled</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-custom">
                    <h5>Ordered Items</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">SKU</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <div class="image">
                                        <img src="{{asset('uploads/products')}}/{{($item->product->image)}}" alt="{{$item->product->name}}">
                                    </div>
                                    <a href="{{route('shop.product.details', ['product_slug' => $item->product->slug])}}" target="_blank">{{$item->product->name}}</a>
                                </td>
                                <td class="text-center">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-center">{{$item->quantity}}</td>
                                <td class="text-center">{{$item->product->SKU}}</td>
                                <td class="text-center">{{$item->product->subcategory->name}}</td>
                                <td class="text-center">{{$item->rstatus ? 'Yes' : 'No'}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{$items->links('pagination::bootstrap-5')}}
                    </div>
                </div>

                <div class="card-custom">
                    <h5>Shipping Address</h5>
                    <p>{{$order->name}}</p>
                    <p>{{$order->address}}, {{$order->locality}}, {{$order->city}}, {{$order->country}}</p>
                    <p>{{$order->landmark}}, {{$order->zip}}</p>
                    <p><strong>Mobile:</strong> {{$order->phone}}</p>
                </div>

                <div class="card-custom">
                    <h5>Transaction Summary</h5>
                    <table class="table">
                        <tr>
                            <th>Subtotal</th><td>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            <th>Tax</th><td>Rp{{ number_format($order->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Discount</th><td>Rp{{ number_format($order->discount, 0, ',', '.') }}</td>
                            <th>Total</th><td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Payment Mode</th><td>{{$transaction->mode}}</td>
                            <th>Status</th>
                            <td>
                                @if($transaction->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($transaction->status == 'declined')
                                    <span class="badge bg-danger">Declined</span>
                                @elseif($transaction->status == 'refunded')
                                    <span class="badge bg-secondary">Refunded</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                @if($order->status == 'ordered')
                <div class="order-detail-actions">
                    <form action="{{route('user.order.cancel')}}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="order_id" value="{{$order->id}}">
                        <button type="button" class="btn btn-danger cancel-order">Cancel Order</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    $(function() {
        $('.cancel-order').on('click', function(e){
            e.preventDefault();
            const form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to cancel this order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
