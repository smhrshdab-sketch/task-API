<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            $table->nullableMorphs('subject');  // the record that changed
            $table->foreignId('causer_id')->constrained('memberships')->restrictOnDelete();
            
            // No separate membership_id column needed!
            
            $table->string('event', 50);
            $table->string('description')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['event']);
            $table->index(['created_at']);
            //$table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
