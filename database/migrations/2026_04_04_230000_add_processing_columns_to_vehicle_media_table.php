<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_media', function (Blueprint $table) {
            $table->string('processing_status')->default('complete')->index()->after('is_primary');
            $table->text('error_message')->nullable()->after('processing_status');
            $table->string('original_disk')->nullable()->after('error_message');
            $table->string('original_path')->nullable()->after('original_disk');
            $table->timestamp('processed_at')->nullable()->after('original_path');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_media', function (Blueprint $table) {
            $table->dropColumn([
                'processing_status',
                'error_message',
                'original_disk',
                'original_path',
                'processed_at',
            ]);
        });
    }
};
