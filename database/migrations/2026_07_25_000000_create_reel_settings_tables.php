<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['reel_categories', 'reel_providers', 'reel_types', 'reel_warehouses'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->string('name')->unique();
                $blueprint->string('short_name')->nullable();
                $blueprint->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $blueprint->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $blueprint->boolean('is_active')->default(true);
                $blueprint->timestamps();
            });
        }

        Schema::create('reel_gsms', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('gsm')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reel_gsms');
        Schema::dropIfExists('reel_warehouses');
        Schema::dropIfExists('reel_types');
        Schema::dropIfExists('reel_providers');
        Schema::dropIfExists('reel_categories');
    }
};
