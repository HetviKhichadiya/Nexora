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
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->comment('0: pending, 1: in_progress, 2: completed')->default(0);
            $table->tinyInteger('priority')->comment('0: low, 1: medium, 2: high')->default(1);  
            $table->date('due_date')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
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
