<?php

namespace App\Helpers;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransHelper
{
    public static function initialize()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public static function createSnapToken($orderData)
    {
        self::initialize();
        
        $transaction = [
            'transaction_details' => [
                'order_id' => $orderData['order_id'],
                'gross_amount' => $orderData['gross_amount'],
            ],
            'customer_details' => [
                'first_name' => $orderData['customer_name'],
                'email' => $orderData['customer_email'],
                'phone' => $orderData['customer_phone'] ?? '',
            ],
            'item_details' => $orderData['items'],
            'callbacks' => [
                'finish' => $orderData['finish_url'],
                'error' => $orderData['error_url'],
                'pending' => $orderData['pending_url'],
            ],
            'expiry' => [
                'start_time' => date("Y-m-d H:i:s O"),
                'unit' => 'minutes',
                'duration' => 1440, // 24 jam
            ]
        ];

        // Tambahkan shipping address jika ada
        if (isset($orderData['shipping_address'])) {
            $transaction['customer_details']['shipping_address'] = $orderData['shipping_address'];
        }

        try {
            $snapToken = Snap::getSnapToken($transaction);
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getStatus($orderId)
    {
        self::initialize();
        
        try {
            $status = \Midtrans\Transaction::status($orderId);
            return $status;
        } catch (\Exception $e) {
            \Log::error('Midtrans Status Error: ' . $e->getMessage());
            return null;
        }
    }

    public static function handleNotification()
    {
        self::initialize();
        
        try {
            $notification = new \Midtrans\Notification();
            
            return [
                'status' => $notification->transaction_status,
                'order_id' => $notification->order_id,
                'payment_type' => $notification->payment_type,
                'fraud_status' => $notification->fraud_status,
                'transaction_id' => $notification->transaction_id,
                'gross_amount' => $notification->gross_amount,
                'raw' => json_decode(json_encode($notification), true)
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return null;
        }
    }
}