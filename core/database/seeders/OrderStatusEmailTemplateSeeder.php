<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class OrderStatusEmailTemplateSeeder extends Seeder
{
    /**
     * Ensures admin status-change templates exist (type must match OrderStatusMailService).
     */
    public function run(): void
    {
        $templates = [
            'Order In Progress' => [
                'subject' => 'Your order is in progress',
                'body' => '<p>Hello {user_name},</p><p>Your order {transaction_number} is now <strong>{order_status}</strong>.</p><p>Order total: {order_cost}</p><p>{site_title}</p>',
            ],
            'Order Shipped' => [
                'subject' => 'Your order has shipped',
                'body' => '<p>Hello {user_name},</p><p>Your order {transaction_number} has been <strong>shipped</strong>.</p><p>Order total: {order_cost}</p><p>{site_title}</p>',
            ],
            'Order Delivered' => [
                'subject' => 'Your order was delivered',
                'body' => '<p>Hello {user_name},</p><p>Your order {transaction_number} has been <strong>delivered</strong>.</p><p>Thank you for shopping with {site_title}.</p>',
            ],
            'Order Cancelled' => [
                'subject' => 'Your order was cancelled',
                'body' => '<p>Hello {user_name},</p><p>Your order {transaction_number} has been <strong>cancelled</strong>.</p><p>{site_title}</p>',
            ],
            'Order Refunded' => [
                'subject' => 'Your order was refunded',
                'body' => '<p>Hello {user_name},</p><p>Your order {transaction_number} has been <strong>refunded</strong>.</p><p>{site_title}</p>',
            ],
        ];

        foreach ($templates as $type => $data) {
            if (EmailTemplate::where('type', $type)->exists()) {
                continue;
            }

            EmailTemplate::create([
                'type' => $type,
                'subject' => $data['subject'],
                'body' => $data['body'],
            ]);
        }
    }
}
