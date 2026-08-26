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
        Schema::create('site_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_id', 100)->unique();
            $table->json('structure');
            $table->unsignedInteger('version')->default(1);
            $table->string('source', 20)->default('manual');
            $table->char('brief_digest', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_structures');
    }
};
