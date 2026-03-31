<?php

namespace Database\Seeders\Tenant;

use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $expenses = [
            ['title' => 'Rice and dry goods restock',    'category' => 'Inventory',    'amount' => 5200,  'date' => '2026-03-01', 'method' => 'GCash',         'status' => 'Paid',      'note' => 'Weekly pantry replenishment'],
            ['title' => 'Milk tea cups and lids',        'category' => 'Packaging',    'amount' => 1850,  'date' => '2026-03-03', 'method' => 'Cash',          'status' => 'Paid',      'note' => 'For drink station packaging'],
            ['title' => 'Electricity contribution',      'category' => 'Utilities',    'amount' => 2400,  'date' => '2026-03-05', 'method' => 'Bank Transfer', 'status' => 'Pending',   'note' => 'March shared stall utilities'],
            ['title' => 'Promo posters and menu print',  'category' => 'Marketing',    'amount' => 1250,  'date' => '2026-03-06', 'method' => 'Cash',          'status' => 'Paid',      'note' => 'Counter display refresh'],
            ['title' => 'Condiments and sauces',         'category' => 'Inventory',    'amount' => 980,   'date' => '2026-03-07', 'method' => 'GCash',         'status' => 'Paid',      'note' => 'Restock for fast-moving items'],
            ['title' => 'Freezer maintenance',           'category' => 'Maintenance',  'amount' => 3200,  'date' => '2026-03-09', 'method' => 'Bank Transfer', 'status' => 'Scheduled', 'note' => 'Scheduled technician visit'],
            ['title' => 'Paper bags and stickers',       'category' => 'Packaging',    'amount' => 1460,  'date' => '2026-03-10', 'method' => 'GCash',         'status' => 'Paid',      'note' => 'Branded takeaway supplies'],
            ['title' => 'Staff meal allowance',          'category' => 'Operations',   'amount' => 1100,  'date' => '2026-03-11', 'method' => 'Cash',          'status' => 'Paid',      'note' => 'Weekend extended hours'],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}
