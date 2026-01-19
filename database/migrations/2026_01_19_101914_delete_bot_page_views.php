<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete WhatsApp link preview requests.
        DB::table('page_views')
            ->where('user_agent', 'like', '%WhatsApp%')
            ->delete();

        // Delete AWS health check requests.
        DB::table('page_views')
            ->where('ip_address', 'like', '34.%')
            ->orWhere('ip_address', 'like', '35.%')
            ->orWhere('ip_address', 'like', '52.%')
            ->orWhere('ip_address', 'like', '54.%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted page views.
    }
};
