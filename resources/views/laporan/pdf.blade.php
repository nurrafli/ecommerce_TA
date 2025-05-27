<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>Laporan Transaksi</h2>
    <table>
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
            @foreach($transactions as $t)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $t->created_at->format('d-m-Y') }}</td>
                <td>{{ $t->user->name ?? '-' }}</td>
                <td>{{ $t->order_id }}</td>
                <td>{{ strtoupper($t->mode) }}</td>
                <td>{{ ucfirst($t->status) }}</td>
                <td>{{ ucfirst($t->payment_status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
