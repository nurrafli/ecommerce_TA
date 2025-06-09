<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use App\Models\Transaction;

class PaymentController extends Controller
{
    public function midtransCallback(Request $request)
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;

        $notification = new Notification();

        $transaction = $notification->transaction_status;
        $orderId = $notification->order_id; 
        $grossAmount = $notification->gross_amount;

        $orderIdParts = explode('-', $orderId);
        if (count($orderIdParts) < 3) {
            return response()->json(['message' => 'Invalid order_id format'], 400);
        }

        $originalOrderId = $orderIdParts[1]; 

        $order = Order::find($originalOrderId);
        if (!$order) {
            return response()->json([
                'message' => 'Order not found'], 404);
        }

        if ($transaction == 'capture' || $transaction == 'settlement') {
            $order->update([
                'status' => 'processing',
                'payment_status' => 'paid',
            ]);
        } elseif ($transaction == 'pending') {
            $order->update([
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
        } elseif (in_array($transaction, ['deny', 'cancel', 'expire'])) {
            $order->update([
                'status' => 'failed',
                'payment_status' => 'unpaid',
            ]);
        }

        \Log::info('Notification received from Midtrans', (array) $notification);
        return response()->json(['message' => 'Callback processed']);
    }
}
