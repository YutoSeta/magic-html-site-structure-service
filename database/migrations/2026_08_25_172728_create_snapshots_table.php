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
        Schema::create('snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_id', 100)->index();
            $table->unsignedBigInteger('sequence');
            $table->string('version', 100);
            $table->char('digest', 64)->index();
            $table->json('document');
            $table->timestampTz('published_at');
            $table->timestamps();

            $table->unique(['site_id', 'sequence']);
            $table->unique(['site_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
