<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set existing BPD records to 'complete' (data sudah lengkap sebelum fitur draft).
     */
    public function up(): void
    {
        // Set semua record yang sudah ada (sebelum fitur draft) ke complete
        DB::table('t_biaya_perjalanan_dinas_header')->update(['vcStatus' => 'complete']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
