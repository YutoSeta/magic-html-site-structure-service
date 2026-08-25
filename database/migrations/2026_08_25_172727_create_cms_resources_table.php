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
        Schema::create('cms_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_id', 100)->index();
            $table->string('type', 32);
            $table->string('resource_key', 100);
            $table->string('name');
            $table->json('schema');
            $table->json('value');
            $table->json('media_refs');
            $table->timestamps();

            $table->unique(['site_id', 'type', 'resource_key']);
            $table->index(['site_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_resources');
    }
};
