<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function midtransCallback(Request $request, MidtransService $midtransService)
    {
        if ($midtransService->isSignatureKeyVerified()) {
            $order = $midtransService->getOrder();
 
            if ($midtransService->getStatus() == 'success') {
                $order->update([
                    'status' => 'processing',
                    'payment_status' => 'paid',
                ]);
 
                $lastPayment = $order->payments()->latest()->first();
                $lastPayment->update([
                    'status' => 'PAID',
                    'paid_at' => now(),
                ]);

                if ($order->transaction) {
                $order->transaction->update([
                    'payment_status' => 'paid',
                ]);
                } else {
                    Log::warning('Transaction not found for order', ['order_id' => $order->id]);
                }
            }
 
            if ($midtransService->getStatus() == 'pending') {
                // lakukan sesuatu jika pembayaran masih pending, seperti mengirim notifikasi ke customer
                // bahwa pembayaran masih pending dan harap selesai pembayarannya
            }
 
            if ($midtransService->getStatus() == 'expire') {
                // lakukan sesuatu jika pembayaran expired, seperti mengirim notifikasi ke customer
                // bahwa pembayaran expired dan harap melakukan pembayaran ulang
            }
 
            if ($midtransService->getStatus() == 'cancel') {
                // lakukan sesuatu jika pembayaran dibatalkan
            }
 
            if ($midtransService->getStatus() == 'failed') {
                // lakukan sesuatu jika pembayaran gagal
            }
 
            return response()
                ->json([
                    'success' => true,
                    'message' => 'Notifikasi berhasil diproses',
                ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }
}