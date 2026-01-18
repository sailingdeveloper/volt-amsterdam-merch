<?php

use App\Models\Cart;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Cart::whereDoesntHave('items')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted carts.
    }
};
