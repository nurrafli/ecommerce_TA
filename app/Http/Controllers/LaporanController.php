<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use PDF; // Import PDF

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'order']);

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return view('admin.laporan', compact('transactions'));
    }

    public function exportPdf(Request $request)
    {
        $query = Transaction::with(['user', 'order']);

        // Filter sama seperti index
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $pdf = PDF::loadView('admin.laporan.pdf', compact('transactions'));
        return $pdf->download('laporan-transaksi.pdf');
    }
}
