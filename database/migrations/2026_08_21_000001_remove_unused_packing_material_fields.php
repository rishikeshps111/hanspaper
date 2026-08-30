<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packing_materials', function (Blueprint $table) {
            $table->dropColumn(['size_mm', 'length_mm', 'width_mm', 'height_mm', 'cover_type']);
        });
    }

    public function down(): void
    {
        Schema::table('packing_materials', function (Blueprint $table) {
            $table->decimal('size_mm', 10, 2)->nullable();
            $table->decimal('length_mm', 10, 2)->nullable();
            $table->decimal('width_mm', 10, 2)->nullable();
            $table->decimal('height_mm', 10, 2)->nullable();
            $table->string('cover_type')->nullable();
        });
    }
};
