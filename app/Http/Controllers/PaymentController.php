<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log; 

class PaymentController extends Controller
{
    public function midtransCallback(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;

        $serverKey = Config::$serverKey;

        $json = $request->all();

        // Validasi Signature Key
        $expectedSignature = hash('sha512',
            $json['order_id'] . $json['status_code'] . $json['gross_amount'] . $serverKey
        );

        if ($json['signature_key'] !== $expectedSignature) {
            Log::warning('Signature key tidak valid.', ['order_id' => $json['order_id']]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        Log::info('Debug signature', [
        'expected' => $expectedSignature,
        'received' => $json['signature_key'],
        'serverKey' => $serverKey,
        'order_id' => $json['order_id'],
        'status_code' => $json['status_code'],
        'gross_amount' => $json['gross_amount']
        ]);

        // Proses update status jika signature valid
        $transactionStatus = $json['transaction_status'];
        $orderIdParts = explode('-', $json['order_id']);
        $originalOrderId = $orderIdParts[2] ?? null;

        $order = Order::with('transaction')->find($originalOrderId);

        if (!$order) {
            Log::warning('Order tidak ditemukan.', ['order_id' => $originalOrderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->transaction) {
            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                $order->transaction->payment_status = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $order->transaction->payment_status = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $order->transaction->payment_status = 'failed';
            }

            $order->transaction->save();
            Log::info('Status transaksi berhasil diperbarui.', [
                'order_id' => $order->id,
                'status' => $order->transaction->payment_status
            ]);
        }

        return response()->json(['message' => 'Callback processed'], 200);
    }
}
