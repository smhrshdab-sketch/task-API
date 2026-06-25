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
        Schema::create('contributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('task')->constrained('tasks')->restrictOnDelete();
            $table->enum('status', ['working', 'suspended','stop'])->default('working');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributes');
    }
};
