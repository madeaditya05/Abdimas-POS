<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 100)->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('kategori_produk')->insert([
            [
                'nama' => 'Coffee',
                'slug' => 'coffee',
                'urutan' => 1,
                'aktif' => true,
                'deskripsi' => 'Kategori minuman berbasis kopi.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama' => 'Non Coffee',
                'slug' => 'non_coffee',
                'urutan' => 2,
                'aktif' => true,
                'deskripsi' => 'Kategori makanan dan minuman non-kopi.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama' => 'Snack',
                'slug' => 'snack',
                'urutan' => 3,
                'aktif' => true,
                'deskripsi' => 'Kategori camilan dan makanan ringan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_produk');
    }
};
