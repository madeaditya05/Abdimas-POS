<?php

return [
    // AKUN KAS & BANK
    'kas'   => '1001',
    'bank'  => '1002',

    // PIUTANG & UTANG
    'piutang_usaha' => '1101',
    'utang_usaha'   => '2001',

    // PENDAPATAN & HPP & PERSEDIAAN
    'pendapatan_penjualan' => '4001',
    'hpp'                   => '5001',   // opsional (kalau nanti aktifkan COGS)
    'persediaan_bahan'     => '1201',

    // Pembelian: pakai kas atau ke utang?
    'pembelian_ke_hutang_default' => false, // true = default ke utang usaha
];
