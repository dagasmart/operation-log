<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admin_operation_log')) {
            Schema::create('admin_operation_log', function (Blueprint $table) {
                $table->engine = 'MyISAM';
                $table->bigIncrements('id')->unsigned();

                $table->bigInteger('user_id');
                $table->string('path');
                $table->string('method', 10);
                $table->string('ip');
                $table->string('module')->nullable();
                $table->text('input');

                $table->timestamps();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_log');
    }
};
