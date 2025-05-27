@extends('layouts.admin')

@section('content')
<div class="container">
    <h1 class="my-4 text-center fw-bold display-5">Laporan Transaksi</h1>

    <form method="GET" action="{{ route('admin.laporan') }}" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">Filter</button>
                <a href="{{ route('admin.laporan.exportPdf', request()->all()) }}" class="btn btn-danger btn-lg w-100">Export PDF</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Order ID</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $t->created_at->format('d-m-Y') }}</td>
                    <td>{{ $t->user->name ?? '-' }}</td>
                    <td class="text-center">{{ $t->order_id }}</td>
                    <td class="text-uppercase text-center">{{ $t->mode }}</td>
                    <td class="text-capitalize text-center">{{ $t->status }}</td>
                    <td class="text-capitalize text-center">{{ $t->payment_status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Tidak ada data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
