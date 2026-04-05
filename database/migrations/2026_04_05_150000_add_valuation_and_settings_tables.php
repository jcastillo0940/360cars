<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        Schema::table('vehicle_valuations', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->change();
            $table->char('currency', 3)->default('CRC')->after('max_price');
            $table->string('share_token')->nullable()->unique()->after('confidence_score');
            $table->boolean('ai_enabled')->default(false)->after('share_token');
            $table->text('ai_summary')->nullable()->after('ai_enabled');
            $table->json('market_insights')->nullable()->after('ai_summary');
            $table->json('algorithm_payload')->nullable()->after('market_insights');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_valuations', function (Blueprint $table) {
            $table->dropColumn(['currency', 'share_token', 'ai_enabled', 'ai_summary', 'market_insights', 'algorithm_payload']);
            $table->foreignId('vehicle_id')->nullable(false)->change();
        });

        Schema::dropIfExists('app_settings');
    }
};
