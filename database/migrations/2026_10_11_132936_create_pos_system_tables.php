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
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 50);
        });

        // 2. Users
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('role_id')->constrained('roles', 'role_id')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password_hash', 255);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
        });

        // 3. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category_name', 100);
            $table->text('description')->nullable();
        });

        // 4. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->string('sku', 50)->unique();
            $table->string('product_name', 150);
            $table->decimal('buy_price', 12, 2);
            $table->decimal('sell_price', 12, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(5);
            $table->dateTime('created_at')->useCurrent();
        });

        // 5. Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('admin_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->dateTime('transaction_date')->useCurrent();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('final_amount', 12, 2);
            $table->string('status', 20);
        });

        // 6. Transaction Items
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->foreignId('transaction_id')->constrained('transactions', 'transaction_id')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });

        // 7. Payment Methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id('payment_method_id');
            $table->string('method_name', 50);
        });

        // 8. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('transaction_id')->constrained('transactions', 'transaction_id')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods', 'payment_method_id')->onDelete('cascade');
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('change_amount', 12, 2)->default(0.00);
            $table->string('payment_status', 20);
            $table->dateTime('payment_date')->useCurrent();
        });

        // 9. Stock Adjustments
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('adjustment_type', ['IN', 'OUT', 'CORRECTION']);
            $table->integer('quantity_changed');
            $table->text('reason')->nullable();
            $table->dateTime('created_at')->useCurrent();
        });

        // 10. Promo Bundles (ABC Optimization)
        Schema::create('promo_bundles', function (Blueprint $table) {
            $table->id('bundle_id');
            $table->string('bundle_name', 150);
            $table->decimal('normal_price', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('promo_price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->useCurrent();
        });

        // 11. Promo Bundle Items
        Schema::create('promo_bundle_items', function (Blueprint $table) {
            $table->id('bundle_item_id');
            $table->foreignId('bundle_id')->constrained('promo_bundles', 'bundle_id')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->integer('quantity')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_bundle_items');
        Schema::dropIfExists('promo_bundles');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};