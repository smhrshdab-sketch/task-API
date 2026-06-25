<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_log_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('audit_log_id')
                ->constrained('audit_logs')
                ->cascadeOnDelete();

            $table->string('field_name');

            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->string('value_type', 50)->nullable();

            $table->timestamps();

            $table->index(['audit_log_id']);
            $table->index(['field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_changes');
    }
};
