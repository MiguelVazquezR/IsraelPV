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
        Schema::create('global_payaments', function (Blueprint $table) {
            $table->id();
            $table->float('initial_amount')->nullable()->unsigned();
            $table->float('amount')->nullable()->unsigned();
            $table->timestamp('date')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_payaments');
    }
};
