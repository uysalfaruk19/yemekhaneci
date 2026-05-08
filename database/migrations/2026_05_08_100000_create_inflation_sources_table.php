<?php

declare(strict_types=1);

/**
 * Migration — Enflasyon kaynak kataloğu.
 * PRD Bölüm 25.3.1 · ADR-013b
 *
 * Resmî kaynaklar (TÜİK / ENAG) ve admin'in oluşturduğu özel formüller
 * (UYSA Et Endeksi vb.) bu tabloda birlikte tutulur.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inflation_sources', function (Blueprint $table) {
            $table->id();

            $table->string('code', 64)->unique()
                ->comment('tuik_tufe, tuik_tufe_gida, uysa_et_endeksi, ...');
            $table->string('name', 150)
                ->comment('Görünür ad: "TÜİK TÜFE Genel"');
            $table->text('description')->nullable();

            $table->enum('source_type', ['tuik_api', 'enag_manual', 'custom_admin'])
                ->comment('tuik_api: EVDS otomatik · enag_manual: aylık manuel · custom_admin: UYSA özel formülü');

            $table->string('tuik_evds_code', 64)->nullable()
                ->comment('Sadece tuik_api için: TP.FG.J0 vb.');
            $table->string('base_period', 10)->nullable()
                ->comment('"2003=100", "2010=100"');
            $table->string('unit', 20)->default('index')
                ->comment('index, tl_kg, tl_lt, ...');

            $table->boolean('is_official')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(100);
            $table->string('color_hex', 7)->nullable()
                ->comment('Grafik rengi: #6B1F2A');

            $table->foreignId('created_by_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'display_order']);
            $table->index('source_type');
        });

        // utf8mb4_turkish_ci collation (Laravel default utf8mb4_unicode_ci üzerine)
        DB::statement('ALTER TABLE inflation_sources CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci');
    }

    public function down(): void
    {
        Schema::dropIfExists('inflation_sources');
    }
};
