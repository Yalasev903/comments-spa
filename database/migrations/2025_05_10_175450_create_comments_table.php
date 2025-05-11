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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
    
            $table->string('user_name');
            $table->string('email');
            $table->string('home_page')->nullable();
    
            $table->text('text'); // Будет содержать HTML
    
            $table->string('attachment_path')->nullable(); // путь к файлу
            $table->enum('attachment_type', ['image', 'text'])->nullable();
    
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
