<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_messages', function (Blueprint $table) {
            $table->id();
            // Tante (Admin-Benutzer) = users.id, das Kind hat kein Konto = NULL
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['video', 'audio', 'picture_book']);
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->text('caption')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['sender_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_messages');
    }
};
