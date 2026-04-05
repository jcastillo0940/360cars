<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_registry_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('plate_number', 30)->nullable()->index();
            $table->string('vendor_source')->default('registro_nacional_cr');
            $table->string('status')->default('pending')->index();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('response_cached')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('internal_market_model');
            $table->decimal('suggested_price', 12, 2);
            $table->decimal('min_price', 12, 2)->nullable();
            $table->decimal('max_price', 12, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('input_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('trade_in_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('offered_amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('status')->default('pending')->index();
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lender_name')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->unsignedSmallInteger('term_months')->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->json('applicant_profile')->nullable();
            $table->json('decision_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('credit_applications');
        Schema::dropIfExists('trade_in_offers');
        Schema::dropIfExists('vehicle_valuations');
        Schema::dropIfExists('vehicle_registry_checks');
    }
};
