<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('name_kana', 200)->nullable();
            $table->enum('category', ['electronics', 'food', 'clothing', 'furniture', 'stationery', 'tools', 'materials', 'other'])->nullable();
            $table->string('spec', 255)->nullable();
            $table->string('maker', 100)->nullable();
            $table->string('jan_code', 13)->nullable()->unique();
            $table->string('unit', 20)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('tax_rate', ['0', '8', '10'])->nullable();
            $table->boolean('has_stock')->default(true);
            $table->enum('status', ['active', 'discontinued'])->default('active');
            $table->text('remarks')->nullable();
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
