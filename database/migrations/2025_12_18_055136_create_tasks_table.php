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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('assignee_id')->constrained('memberships')->restrictOnDelete();
            $table->string('title');
            $table->foreignId('parent')->nullable()->constrained('tasks')->restrictOnDelete();
            $table->integer('path')->default(1)->index();
            $table->text('description')->nullable();
            $table->enum('status', ['preparation', 'progressing','completed','stopped','suspended'])->default('preparation');
            $table->string('priority')->default('normal');
            $table->date('deadline')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
