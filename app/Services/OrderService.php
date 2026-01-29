<?php

namespace App\Services;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    /**
     * Mark an order as paid and process it.
     */
    public function markOrderPaid(Order $order): void
    {
        if ($order->status === 'paid') {
            return;
        }

        $order->update(['status' => 'paid']);

        $this->decrementStock($order);
        $this->sendOrderConfirmationEmail($order);
    }

    /**
     * Decrement stock for each order item.
     */
    protected function decrementStock(Order $order): void
    {
        foreach ($order->item as $orderItem) {
            if ($orderItem->product !== null) {
                $orderItem->product->decrementStockForSize(
                    $orderItem->size ?? '',
                    $orderItem->quantity
                );
            }
        }
    }

    /**
     * Send order confirmation email to the customer and notification to all admins.
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            // Send confirmation to customer.
            if ($order->customer_email !== null) {
                Mail::to($order->customer_email)->send(new OrderConfirmation($order));
            }

            // Send notification to all admins.
            $adminEmails = User::pluck('email')->toArray();
            foreach ($adminEmails as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminOrderNotification($order));
            }
        } catch (\Exception $e) {
            // Log the error but don't break the checkout flow.
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
