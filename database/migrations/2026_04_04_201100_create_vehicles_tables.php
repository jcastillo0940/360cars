<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $supportsFullText = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'], true);

        Schema::create('vehicles', function (Blueprint $table) use ($supportsFullText) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_make_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('vin', 32)->nullable()->index();
            $table->string('plate_number', 30)->nullable()->index();
            $table->string('condition')->default('used')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->string('trim')->nullable();
            $table->string('body_type')->index();
            $table->string('fuel_type')->index();
            $table->string('transmission')->index();
            $table->string('drivetrain')->nullable();
            $table->unsignedInteger('mileage')->nullable()->index();
            $table->string('mileage_unit', 10)->default('km');
            $table->string('engine')->nullable();
            $table->decimal('engine_size', 3, 1)->nullable();
            $table->string('exterior_color')->nullable();
            $table->string('interior_color')->nullable();
            $table->unsignedTinyInteger('doors')->nullable();
            $table->unsignedTinyInteger('seats')->nullable();
            $table->decimal('price', 12, 2)->index();
            $table->char('currency', 3)->default('USD');
            $table->decimal('original_price', 12, 2)->nullable();
            $table->decimal('market_price', 12, 2)->nullable();
            $table->string('price_badge')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->char('country_code', 2)->default('CR')->index();
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description');
            $table->json('features')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('publication_tier')->default('basic')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_verified_plate')->default(false)->index();
            $table->boolean('supports_360')->default(false);
            $table->boolean('has_video')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('lead_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            if ($supportsFullText) {
                $table->fullText(['title', 'description']);
            }
        });

        Schema::create('vehicle_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('disk')->default('r2');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false)->index();
            $table->json('conversions')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_lifestyle_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lifestyle_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(100);
            $table->timestamps();

            $table->unique(['vehicle_id', 'lifestyle_category_id'], 'veh_lifestyle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_lifestyle_category');
        Schema::dropIfExists('vehicle_media');
        Schema::dropIfExists('vehicles');
    }
};
