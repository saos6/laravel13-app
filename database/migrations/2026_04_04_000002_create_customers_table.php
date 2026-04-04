<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('name_kana', 200)->nullable();
            $table->string('postal_code', 8)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->tinyInteger('closing_day')->unsigned()->nullable()->comment('締め日（31=末日）');
            $table->enum('payment_cycle', ['monthly', 'bimonthly', 'quarterly', 'annually'])->nullable();
            $table->tinyInteger('payment_day')->unsigned()->nullable()->comment('支払日（31=末日）');
            $table->text('remarks')->nullable();
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
