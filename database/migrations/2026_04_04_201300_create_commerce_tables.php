<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('audience')->default('private_seller')->index();
            $table->decimal('price', 10, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('billing_interval')->nullable()->index();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedInteger('max_active_listings')->nullable();
            $table->unsignedSmallInteger('photo_limit')->nullable();
            $table->boolean('allows_video')->default(false);
            $table->boolean('allows_360')->default(false);
            $table->boolean('supports_credit_leads')->default(false);
            $table->boolean('supports_trade_in')->default(false);
            $table->unsignedSmallInteger('priority_weight')->default(0)->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('auto_renews')->default(false);
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('external_reference')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('payable');
            $table->string('provider')->index();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('external_reference')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
