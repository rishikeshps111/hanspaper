<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE production_item_masters MODIFY status ENUM('Pending','Completed','Partial','Progress','In Progress','Cancelled','Packing Pending','Assigning Pending') NOT NULL");
        DB::statement("ALTER TABLE production_item_masters MODIFY production_status ENUM('Pending','Completed','Partial','In Progress','Cancelled') NOT NULL");

        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('reel_stock_id');
            // The legacy machines table uses a signed BIGINT primary key.
            $table->bigInteger('machine_id');
            $table->unsignedBigInteger('production_user_id');
            $table->string('source_reel_status', 20);
            $table->decimal('output_roll_width', 12, 3);
            $table->decimal('roll_length', 12, 3);
            $table->string('status', 20)->default('in_progress');
            $table->unsignedTinyInteger('active_key')->nullable()->default(1);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('finished_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['production_id', 'active_key']);
            $table->unique(['reel_stock_id', 'active_key']);
            $table->unique(['machine_id', 'active_key']);
            $table->foreign('production_id')->references('id')->on('production_item_masters')->restrictOnDelete();
            $table->foreign('reel_stock_id')->references('id')->on('reel_stocks')->restrictOnDelete();
            $table->foreign('machine_id')->references('id')->on('machines')->restrictOnDelete();
            // employees is a legacy MyISAM table, so MySQL cannot create a foreign key to it.
        });

        Schema::table('production_list', function (Blueprint $table) {
            $table->foreignId('production_run_id')->nullable()->after('production_item_master_id')->constrained('production_runs')->nullOnDelete();
        });
        Schema::table('reel_stock_usages', function (Blueprint $table) {
            $table->foreignId('production_run_id')->nullable()->after('production_id')->constrained('production_runs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reel_stock_usages', fn(Blueprint $table) => $table->dropConstrainedForeignId('production_run_id'));
        Schema::table('production_list', fn(Blueprint $table) => $table->dropConstrainedForeignId('production_run_id'));
        Schema::dropIfExists('production_runs');
        DB::statement("ALTER TABLE production_item_masters MODIFY status ENUM('Pending','Completed','Partial','Progress','Cancelled','Packing Pending','Assigning Pending') NOT NULL");
        DB::statement("ALTER TABLE production_item_masters MODIFY production_status ENUM('Pending','Completed','Partial','Cancelled') NOT NULL");
    }
};
