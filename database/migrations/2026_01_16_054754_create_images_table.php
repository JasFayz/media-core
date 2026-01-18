<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('raw_hash', 64)->index();
            $table->string('original_name');
            $table->string('original_mime', 50);
            $table->string('original_path')->nullable();
            $table->unsignedBigInteger('original_size')->nullable();

            $table->string('hash', 64)->index()->nullable();
            $table->string('mime', 50)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('path')->nullable()->index();

            $table->string('status')->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'hash']);
            $table->unique(['user_id', 'raw_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
