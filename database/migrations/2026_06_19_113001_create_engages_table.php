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
        Schema::create('engages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contributed_by')->constrained('memberships')->cascadeOnDelete();
            $table->foreignId('task')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('contributor')->constrained('memberships')->restrictOnDelete();
            $table->enum('status', ['active', 'suspended','inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engages');
    }
};
