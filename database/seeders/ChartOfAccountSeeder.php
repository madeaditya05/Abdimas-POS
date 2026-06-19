<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'code'        => '1001',
                'name'        => 'Kas',
                'type'        => 'asset',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '1002',
                'name'        => 'Bank',
                'type'        => 'asset',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '1101',
                'name'        => 'Piutang Usaha',
                'type'        => 'asset',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '1201',
                'name'        => 'Persediaan Bahan',
                'type'        => 'asset',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '2001',
                'name'        => 'Hutang Usaha',
                'type'        => 'liability',
                'normal_side' => 'credit',
                'is_active'   => true,
            ],
            [
                'code'        => '4001',
                'name'        => 'Pendapatan Penjualan',
                'type'        => 'revenue',
                'normal_side' => 'credit',
                'is_active'   => true,
            ],
            [
                'code'        => '5001',
                'name'        => 'HPP Bahan Baku',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '5100',
                'name'        => 'Pembelian Bahan',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '6001',
                'name'        => 'Beban Gaji',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '6002',
                'name'        => 'Beban Listrik',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '6003',
                'name'        => 'Beban Sewa',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
            [
                'code'        => '6004',
                'name'        => 'Beban Promosi',
                'type'        => 'expense',
                'normal_side' => 'debit',
                'is_active'   => true,
            ],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::updateOrCreate(
                ['code' => $acc['code']],
                [
                    'name'        => $acc['name'],
                    'type'        => $acc['type'],
                    'normal_side' => $acc['normal_side'],
                    'is_active'   => $acc['is_active'],
                ]
            );
        }
    }
}
