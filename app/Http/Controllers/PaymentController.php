<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function midtransCallback(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;

        Log::info('📩 Midtrans callback HIT', $request->all());

        $json = $request->all();

        // Ambil data dengan aman
        $signatureKey = data_get($json, 'signature_key');
        $orderId = data_get($json, 'order_id');
        $statusCode = data_get($json, 'status_code');
        $grossAmount = data_get($json, 'gross_amount');

        if (!$signatureKey || !$orderId || !$statusCode || !$grossAmount) {
            Log::error('❌ Payload tidak lengkap.', $json);
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // Ambil server key sebelum hashing
        $serverKey = Config::$serverKey;

        // Format ulang gross amount agar cocok untuk signature
        $formattedGrossAmount = number_format((float)$grossAmount, 0, '', '');

        // Validasi signature
        $expectedSignature = hash('sha512',
            (string)$orderId .
            (string)$statusCode .
            $formattedGrossAmount .
            $serverKey
        );

        Log::info('🔐 Signature check', [
            'expected' => $expectedSignature,
            'received' => $signatureKey
        ]);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('🚫 Signature key tidak valid.', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Ambil ID order asli (contoh: ORDER-WEB-123 -> ambil 123)
        $orderIdParts = explode('-', $orderId);
        $originalOrderId = $orderIdParts[2] ?? null;

        if (!$originalOrderId || !is_numeric($originalOrderId)) {
            Log::warning('⚠️ Format order_id tidak valid.', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid order_id format'], 400);
        }

        $order = Order::with('transaction')->find($originalOrderId);

        if (!$order) {
            Log::warning('❓ Order tidak ditemukan.', ['order_id' => $originalOrderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update status pembayaran
        $transactionStatus = $json['transaction_status'];

        if ($order->transaction) {
            switch ($transactionStatus) {
                case 'capture':
                case 'settlement':
                    $order->transaction->payment_status = 'paid';
                    break;
                case 'pending':
                    $order->transaction->payment_status = 'pending';
                    break;
                case 'deny':
                case 'cancel':
                case 'expire':
                    $order->transaction->payment_status = 'failed';
                    break;
                default:
                    Log::warning('⚠️ Status transaksi tidak dikenali.', ['status' => $transactionStatus]);
                    break;
            }

            $order->transaction->save();

            Log::info('✅ Status transaksi diperbarui.', [
                'order_id' => $order->id,
                'status' => $order->transaction->payment_status
            ]);
        }

        return response()->json(['message' => 'Callback processed'], 200);
    }
}