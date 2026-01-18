<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->string('billing_address_line1')->nullable()->after('customer_phone');
            $table->string('billing_address_line2')->nullable()->after('billing_address_line1');
            $table->string('billing_city')->nullable()->after('billing_address_line2');
            $table->string('billing_postal_code')->nullable()->after('billing_city');
            $table->string('billing_country')->nullable()->after('billing_postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_phone',
                'billing_address_line1',
                'billing_address_line2',
                'billing_city',
                'billing_postal_code',
                'billing_country',
            ]);
        });
    }
};
