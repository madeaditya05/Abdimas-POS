<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Penjualan;
use App\Models\PembelianBahan;
use App\Services\JournalPoster;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeeklySalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Order::where('order_no', 'like', 'DEMO-WEEK-%')->delete();
            DB::table('penjualan')
                ->where('kode_penjualan', 'like', 'DEMO-OFFLINE-%')
                ->delete();

            $products = collect([
                ['sku' => 'DEMO-NK', 'name' => 'Nasi Katsu', 'price' => 22000],
                ['sku' => 'DEMO-SC', 'name' => 'Spaghetti Carbonara', 'price' => 20000],
                ['sku' => 'DEMO-ET', 'name' => 'Es Teh Manis', 'price' => 5000],
                ['sku' => 'DEMO-KS', 'name' => 'Kopi Susu', 'price' => 12000],
                ['sku' => 'DEMO-CB', 'name' => 'Chicken Bites', 'price' => 15000],
                ['sku' => 'DEMO-RB', 'name' => 'Rice Bowl Ayam', 'price' => 22000],
            ])->mapWithKeys(function (array $item) {
                $product = Product::updateOrCreate(
                    ['sku' => $item['sku']],
                    [
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'stock' => 100,
                        'min_stock' => 10,
                        'is_active' => true,
                    ]
                );

                return [$item['sku'] => $product];
            })->values();

            $customers = collect(['Alya', 'Bima', 'Citra', 'Dimas', 'Eka', 'Farah'])
                ->map(fn (string $name) => Customer::updateOrCreate(['name' => $name], []))
                ->values();

            $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $ordersPerDay = [4, 5, 6, 7, 8, 6, 5];
            $offlineOrdersPerDay = [3, 4, 5, 4, 6, 5, 3];
            $userId = (int) (DB::table('users')->value('id') ?? 1);

            foreach ($ordersPerDay as $dayIndex => $orderCount) {
                $day = $weekStart->copy()->addDays($dayIndex);

                for ($orderIndex = 1; $orderIndex <= $orderCount; $orderIndex++) {
                    $orderTime = $day->copy()
                        ->setTime(9 + (($orderIndex * 2) % 10), (15 * $orderIndex) % 60);

                    $orderItems = [];
                    $itemCount = 2 + (($dayIndex + $orderIndex) % 2);
                    $subtotal = 0;

                    for ($itemIndex = 0; $itemIndex < $itemCount; $itemIndex++) {
                        $product = $products[($dayIndex + $orderIndex + $itemIndex) % $products->count()];
                        $qty = 1 + ((($dayIndex + $orderIndex + $itemIndex) % 3) === 0 ? 1 : 0);
                        $lineTotal = (int) $product->price * $qty;
                        $subtotal += $lineTotal;

                        $orderItems[] = [
                            'product' => $product,
                            'qty' => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }

                    $discount = (($dayIndex + $orderIndex) % 5 === 0) ? 3000 : 0;
                    $grandTotal = max(0, $subtotal - $discount);

                    $order = Order::forceCreate([
                        'order_no' => 'DEMO-WEEK-' . $day->format('Ymd') . '-' . str_pad((string) $orderIndex, 3, '0', STR_PAD_LEFT),
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'tax' => 0,
                        'grand_total' => $grandTotal,
                        'status' => 'paid',
                        'customer_id' => $customers[($dayIndex + $orderIndex) % $customers->count()]->id,
                        'payment_method' => $orderIndex % 2 === 0 ? 'qris' : 'cash',
                        'created_at' => $orderTime,
                        'updated_at' => $orderTime,
                    ]);

                    foreach ($orderItems as $item) {
                        OrderItem::forceCreate([
                            'order_id' => $order->id,
                            'product_id' => $item['product']->id,
                            'name' => $item['product']->name,
                            'price' => (int) $item['product']->price,
                            'qty' => $item['qty'],
                            'line_total' => $item['line_total'],
                            'created_at' => $orderTime,
                            'updated_at' => $orderTime,
                        ]);
                    }
                }

                for ($offlineIndex = 1; $offlineIndex <= $offlineOrdersPerDay[$dayIndex]; $offlineIndex++) {
                    $orderTime = $day->copy()
                        ->setTime(8 + (($offlineIndex * 3) % 11), (10 * $offlineIndex) % 60);
                    $baseTotal = 28000 + (($dayIndex + $offlineIndex) % 5) * 7000;
                    $discount = (($dayIndex + $offlineIndex) % 6 === 0) ? 2000 : 0;
                    $total = max(0, $baseTotal - $discount);

                    DB::table('penjualan')->insert([
                        'kode_penjualan' => 'DEMO-OFFLINE-' . $day->format('Ymd') . '-' . str_pad((string) $offlineIndex, 3, '0', STR_PAD_LEFT),
                        'tanggal' => $orderTime,
                        'user_id' => $userId,
                        'customer_id' => $customers[($dayIndex + $offlineIndex) % $customers->count()]->id,
                        'total' => $total,
                        'subtotal_sebelum_diskon' => $baseTotal,
                        'diskon_persen' => 0,
                        'diskon_nominal' => $discount,
                        'bayar' => $total,
                        'kembalian' => 0,
                        'metode' => $offlineIndex % 2 === 0 ? 'qris' : 'cash',
                        'invoice_to_name' => null,
                        'invoice_to_company' => null,
                        'tempo_due_date' => null,
                        'struk_dicetak' => true,
                        'struk_dicetak_at' => $orderTime,
                        'created_at' => $orderTime,
                        'updated_at' => $orderTime,
                    ]);
                }
            }

            // Post journal entries for all Penjualan records
            $poster = app(JournalPoster::class);
            Penjualan::with(['details.produk', 'user', 'customer'])->chunk(50, function ($sales) use ($poster) {
                foreach ($sales as $sale) {
                    $poster->postForPenjualan($sale);
                }
            });

            // Post journal entries for all PembelianBahan records
            PembelianBahan::with(['details'])->chunk(50, function ($buys) use ($poster) {
                foreach ($buys as $buy) {
                    $poster->postForPembelianBahan($buy);
                }
            });
        });
    }
}
