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
        Schema::create('updates', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->text('description')->nullable();
            $table->string('download_url');
            $table->text('release_notes')->nullable();
            $table->boolean('is_installed')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('checksum')->nullable();
            $table->string('requires_version')->nullable();
            $table->timestamps();
            
            $table->unique('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updates');
    }
};
