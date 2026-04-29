<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_image')->nullable();
            $table->string('robots', 40)->nullable();
            $table->string('schema_type', 40)->nullable();
            $table->string('focus_keyword', 160)->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
