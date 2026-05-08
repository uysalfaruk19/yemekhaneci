<?php

declare(strict_types=1);

/**
 * Migration — Aylık endeks değerleri.
 * PRD Bölüm 25.3.2 · ADR-013b
 *
 * Bir kaynak için ay başına bir kayıt; UPSERT için unique anahtar.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inflation_indices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained('inflation_sources')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');

            $table->decimal('index_value', 14, 4)
                ->comment('Resmî: baz dönem=100; özel: ham değer (tl/kg vb.)');
            $table->decimal('monthly_change_pct', 7, 4)->nullable();
            $table->decimal('yearly_change_pct', 7, 4)->nullable();

            $table->timestamp('fetched_at')->nullable()
                ->comment('EVDS API ile çekildiyse zaman damgası');
            $table->foreignId('entered_by_admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('source_url', 500)->nullable();
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->unique(['source_id', 'period_year', 'period_month'], 'uniq_source_period');
            $table->index(['period_year', 'period_month']);
        });

        DB::statement('ALTER TABLE inflation_indices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci');

        // Yıl/ay aralık kontrolü (CHECK constraint)
        DB::statement('ALTER TABLE inflation_indices ADD CONSTRAINT chk_period_year CHECK (period_year BETWEEN 2003 AND 2099)');
        DB::statement('ALTER TABLE inflation_indices ADD CONSTRAINT chk_period_month CHECK (period_month BETWEEN 1 AND 12)');
    }

    public function down(): void
    {
        Schema::dropIfExists('inflation_indices');
    }
};
