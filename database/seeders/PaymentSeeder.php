<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Payment Methods
        $paymentMethods = [
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'type' => 'online',
                'description' => 'Pay using Visa, Mastercard, or American Express',
                'icon' => 'fas fa-credit-card',
                'processing_fee' => 2.9,
                'processing_fee_type' => 'percentage',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'type' => 'online',
                'description' => 'Pay securely with your PayPal account',
                'icon' => 'fab fa-paypal',
                'processing_fee' => 3.49,
                'processing_fee_type' => 'percentage',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'type' => 'offline',
                'description' => 'Direct bank transfer to our account',
                'icon' => 'fas fa-university',
                'processing_fee' => 0,
                'processing_fee_type' => 'fixed',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Cash',
                'code' => 'cash',
                'type' => 'offline',
                'description' => 'Pay in cash at our office',
                'icon' => 'fas fa-money-bill-wave',
                'processing_fee' => 0,
                'processing_fee_type' => 'fixed',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'ABA Pay',
                'code' => 'aba_pay',
                'type' => 'online',
                'description' => 'Pay using ABA mobile banking',
                'icon' => 'fas fa-mobile-alt',
                'processing_fee' => 0,
                'processing_fee_type' => 'fixed',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Wing',
                'code' => 'wing',
                'type' => 'online',
                'description' => 'Pay using Wing mobile money',
                'icon' => 'fas fa-dove',
                'processing_fee' => 1,
                'processing_fee_type' => 'percentage',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Bitcoin',
                'code' => 'bitcoin',
                'type' => 'crypto',
                'description' => 'Pay with Bitcoin cryptocurrency',
                'icon' => 'fab fa-bitcoin',
                'processing_fee' => 1,
                'processing_fee_type' => 'percentage',
                'is_active' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Cheque',
                'code' => 'cheque',
                'type' => 'offline',
                'description' => 'Pay by cheque',
                'icon' => 'fas fa-money-check',
                'processing_fee' => 5,
                'processing_fee_type' => 'fixed',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        $this->command->info('Created '.count($paymentMethods).' payment methods');

        // Get users for creating invoices and transactions
        $users = User::take(5)->get();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping invoice and transaction seeding.');

            return;
        }

        $paymentMethodModels = PaymentMethod::where('is_active', true)->get();

        // Create Sample Invoices
        $invoices = [];
        $statuses = ['draft', 'sent', 'paid', 'paid', 'sent'];
        $customerNames = [
            'Sokha Chan', 'Dara Kim', 'Sopheap Ly', 'Chanthy Ros', 'Vanna Pich',
            'Bopha Sok', 'Rith Chea', 'Srey Mom', 'Kosal Tep', 'Maly Yun',
        ];

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $subtotal = rand(500, 50000);
            $taxRate = rand(0, 10);
            $taxAmount = $subtotal * ($taxRate / 100);
            $discount = rand(0, 100);
            $total = $subtotal + $taxAmount - $discount;
            $status = $statuses[array_rand($statuses)];

            $issueDate = now()->subDays(rand(1, 60));
            $dueDate = (clone $issueDate)->addDays(30);

            $invoice = Invoice::create([
                // Set explicitly because DatabaseSeeder uses WithoutModelEvents,
                // which suppresses the Invoice::creating hook that auto-fills this.
                'invoice_number' => 'INV-'.date('Ym').str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'customer_name' => $customerNames[$i],
                'customer_email' => strtolower(str_replace(' ', '.', $customerNames[$i])).'@example.com',
                'customer_phone' => '+855 '.rand(10, 99).' '.rand(100, 999).' '.rand(1000, 9999),
                'customer_address' => 'Phnom Penh, Cambodia',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'currency' => 'USD',
                'status' => $status,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'paid_date' => $status === 'paid' ? $dueDate->subDays(rand(1, 10)) : null,
                'notes' => 'Thank you for your business!',
                'terms' => 'Payment due within 30 days.',
            ]);

            $invoices[] = $invoice;
        }

        $this->command->info('Created 10 sample invoices');

        // Create Sample Transactions
        $transactionTypes = ['payment', 'payment', 'payment', 'refund', 'deposit'];
        $transactionStatuses = ['completed', 'completed', 'pending', 'completed', 'failed'];

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $paymentMethod = $paymentMethodModels->random();
            $invoice = rand(0, 1) ? $invoices[array_rand($invoices)] : null;
            $type = $transactionTypes[array_rand($transactionTypes)];
            $status = $transactionStatuses[array_rand($transactionStatuses)];

            $amount = $invoice ? $invoice->total_amount : rand(100, 10000);
            $fee = $paymentMethod->calculateFee($amount);
            $netAmount = $amount - $fee;

            Transaction::create([
                // Set explicitly because DatabaseSeeder uses WithoutModelEvents,
                // which suppresses the Transaction::creating hook that auto-fills this.
                'transaction_id' => 'TXN-'.date('Ym').str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'invoice_id' => $invoice?->id,
                'payment_method_id' => $paymentMethod->id,
                'type' => $type,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'currency' => 'USD',
                'status' => $status,
                'description' => $type === 'payment' ? 'Payment for real estate services' : 'Refund for cancelled booking',
                'ip_address' => '192.168.1.'.rand(1, 255),
                'processed_at' => $status === 'completed' ? now()->subDays(rand(0, 30)) : null,
            ]);
        }

        $this->command->info('Created 20 sample transactions');
    }
}
