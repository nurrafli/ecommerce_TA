<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use Exception;
use Illuminate\Support\Str;

class MidtransService
{
    protected string $serverKey;
    protected bool $isProduction;
    protected bool $isSanitized;
    protected bool $is3ds;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->isProduction = config('midtrans.is_production');
        $this->isSanitized = config('midtrans.is_sanitized');
        $this->is3ds = config('midtrans.is_3ds');

        Config::$serverKey = $this->serverKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }

    public function createSnapToken(Order $order): string
    {
        $itemDetails = $this->mapItemsToDetails($order);

        // Hitung total otomatis dari item_details
        $grossAmount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $itemDetails));

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_id,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $this->getCustomerDetails($order),
        ];
        try {
            return Snap::getSnapToken($params);
        } catch (Exception $e) {
            throw new Exception('Failed to create Snap token: ' . $e->getMessage());
        }
    }

    public function isSignatureKeyVerified(): bool
    {
        $notification = new Notification();

        $localSignatureKey = hash('sha512',
            $notification->order_id .
            $notification->status_code .
            $notification->gross_amount .
            $this->serverKey
        );

        return $localSignatureKey === $notification->signature_key;
    }

    public function getOrder(): ?Order
    {
        $notification = new Notification();

        return Order::where('order_id', $notification->order_id)->first();
    }

    public function getStatus(): string
    {
        $notification = new Notification();
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        return match ($transactionStatus) {
            'capture' => ($fraudStatus == 'accept') ? 'success' : 'pending',
            'settlement' => 'success',
            'deny' => 'failed',
            'cancel' => 'cancel',
            'expire' => 'expire',
            'pending' => 'pending',
            default => 'unknown',
        };
    }

    protected function mapItemsToDetails(Order $order): array
    {
        return $order->items->map(function ($item) {
            return [
                'id' => (string) $item->product_id,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => substr($item->product_name, 0, 50), // <= ini fix-nya
            ];
        })->toArray();
    }


    protected function getCustomerDetails(Order $order): array
    {
        $user = $order->user;

        return [
            'first_name' => $user->name ?? 'Guest',
            'email' => $user->email ?? 'guest@example.com',
            'phone' => $order->phone ?? '081000000000',
        ];
    }
}
