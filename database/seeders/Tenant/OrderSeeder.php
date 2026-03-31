<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get customers from CENTRAL DB
        $customers = DB::connection('central')
            ->table('users')
            ->pluck('id');

        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty() || $customers->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];

        // create 20 orders per tenant (adjust as needed)
        for ($i = 0; $i < 20; $i++) {

            $userId = $customers->random();

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => fake()->randomElement($statuses),
                'placed_at' => now()->subDays(rand(0, 30)),
                'total' => 0, // temp
            ]);

            $total = 0;

            // 1–5 items per order
            $itemsCount = rand(1, 5);

            $selectedProducts = $products->shuffle()->take($itemsCount);

            foreach ($selectedProducts as $product) {

                $qty = fake()->numberBetween(1, 5);
                $lineTotal = $product->price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            // update order total
            $order->update([
                'total' => $total
            ]);

            // CENTRAL SYNC (customer_orders) — map tenant status to customer vocabulary
            $statusMap = [
                'pending'   => 'pending',
                'confirmed' => 'pending',
                'preparing' => 'preparing',
                'ready'     => 'ready_for_pickup',
                'completed' => 'picked_up',
                'cancelled' => 'cancelled',
            ];

            DB::connection('central')->table('customer_orders')->insert([
                'user_id' => $userId,
                'tenant_id' => tenant('id'),
                'order_id' => $order->id,
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'total' => $total,
                'status' => $statusMap[$order->status] ?? $order->status,
                'ordered_at' => $order->placed_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}