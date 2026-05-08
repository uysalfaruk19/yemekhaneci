<?php

declare(strict_types=1);

/**
 * Migration — Hesaplayıcı sorgu kayıtları.
 * PRD Bölüm 25.3.3 · ADR-013b
 *
 * Anonim ve lead capture'lı sorguların KVKK uyumlu kaydı + analytics.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inflation_calculations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained('inflation_sources')
                ->nullOnDelete();

            $table->date('start_date');
            $table->decimal('start_price', 14, 2);
            $table->date('end_date');
            $table->decimal('end_price', 14, 2)
                ->comment('Hesaplanmış sonuç (idempotent log için)');
            $table->decimal('change_pct', 8, 4)
                ->comment('Toplam yüzde değişim');

            $table->string('email', 255)->nullable();
            $table->timestamp('kvkk_accepted_at')->nullable();
            $table->binary('ip_address', 16)
                ->comment('IPv4 (4 byte) veya IPv6 (16 byte)');
            $table->string('user_agent', 500)->nullable();

            $table->enum('panel_origin', ['public', 'supplier', 'admin'])
                ->default('public');

            $table->timestamp('created_at')->useCurrent();

            $table->index('email');
            $table->index('created_at');
            $table->index('panel_origin');
            $table->index(['source_id', 'created_at']);
        });

        DB::statement('ALTER TABLE inflation_calculations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci');
    }

    public function down(): void
    {
        Schema::dropIfExists('inflation_calculations');
    }
};
