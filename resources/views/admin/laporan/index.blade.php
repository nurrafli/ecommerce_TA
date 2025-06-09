@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Laporan Transaksi</h1>

    <form method="GET" action="{{ route('admin.laporan') }}" class="mb-4">
        <div class="row g-2">
            <div class="col">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col">
                <label>Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.laporan.exportPdf', request()->all()) }}" class="btn btn-danger ms-2">Export PDF</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $t->created_at->format('d-m-Y') }}</td>
                <td>{{ $t->user->name ?? '-' }}</td>
                <td>{{ $t->order_id }}</td>
                <td>{{ strtoupper($t->mode) }}</td>
                <td>{{ ucfirst($t->status) }}</td>
                <td>{{ ucfirst($t->payment_status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
