<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('workflows.db_prefix').'task_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workflow_log_id');
            $table->bigInteger('task_id');
            $table->string('name');
            $table->string('status');
            $table->text('message')->nullable();
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('workflows.db_prefix').'task_logs');
    }
}
