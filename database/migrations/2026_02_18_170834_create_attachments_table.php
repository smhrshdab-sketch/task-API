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
        Schema::create('attachments', function (Blueprint $table) {

            $table->id();

            // Polymorphic relation
            $table->morphs('attachable'); 
            // attachable_id
            // attachable_type

            // File metadata
            $table->string('original_name');
            $table->string('file_name')->unique();
            $table->string('file_path');
            $table->string('disk')->default('public');

            $table->string('mime_type')->index();
            $table->unsignedBigInteger('size'); // bytes

            // امنیت
            $table->foreignId('uploaded_by')->constrained('memberships')->onDelete('cascade');

            $table->boolean('is_public')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Index برای performance
            $table->index(['attachable_id', 'attachable_type']);
            $table->index(['uploaded_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
