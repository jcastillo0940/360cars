<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cantons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('canton_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->string('province')->nullable()->after('city');
            $table->string('canton')->nullable()->after('province');
            $table->string('district')->nullable()->after('canton');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropColumn(['province', 'canton', 'district']);
        });

        Schema::dropIfExists('districts');
        Schema::dropIfExists('cantons');
        Schema::dropIfExists('provinces');
    }
};
