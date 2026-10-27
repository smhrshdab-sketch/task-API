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
        Schema::create('membership_permission', function (Blueprint $table) {
            $table->foreignId('membership_id')
                ->constrained('memberships')
                ->cascadeOnDelete();
        
            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();
        
            $table->primary(['membership_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_permission');
    }
};
