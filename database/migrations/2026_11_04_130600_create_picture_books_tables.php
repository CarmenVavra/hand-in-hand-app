<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picture_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        Schema::create('picture_book_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picture_book_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('audio_path');
            $table->string('label')->nullable();
            $table->unsignedInteger('order_column')->default(0);
            $table->timestamps();

            $table->index(['picture_book_id', 'order_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picture_book_pages');
        Schema::dropIfExists('picture_books');
    }
};
