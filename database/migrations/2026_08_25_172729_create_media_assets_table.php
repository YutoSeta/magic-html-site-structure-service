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
        Schema::create('media_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cms_resource_id')->constrained()->cascadeOnDelete();
            $table->string('site_id', 100)->index();
            $table->string('media_key', 100);
            $table->string('object_key', 2048)->unique();
            $table->string('original_name');
            $table->string('mime', 255)->index();
            $table->unsignedBigInteger('bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->char('sha256', 64);
            $table->timestampTz('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['site_id', 'media_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
