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
        DB::table('page_views')
            ->where('user_agent', 'like', '%Google-Read-Aloud%')
            ->orWhere('user_agent', 'like', '%Googlebot%')
            ->orWhere('user_agent', 'like', '%AdsBot%')
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
