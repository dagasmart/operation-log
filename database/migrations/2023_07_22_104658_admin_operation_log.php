<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    private $table = 'admin_operation_log';

    public function up(): void
    {
        if (Schema::hasTable($this->table)) {
            //备份
            Schema::rename($this->table, 'backup_' . $this->table . '_' .date('YmdHis'));
            //删除
            Schema::dropIfExists($this->table);
        }
        //创建
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                //$table->engine = 'MyISAM';
                $table->comment('操作日志记录表');
                $table->bigIncrements('id')->unsigned();
                $table->bigInteger('user_id')->default(0)->comment('用户ID');
                $table->string('user_name',50)->nullable()->comment('用户名');
                $table->string('path')->comment('请求地址');
                $table->string('method', 10)->default('GET')->comment('请求方法');
                $table->string('ip')->nullable()->comment('IP');
                $table->text('input')->comment('请求数据');

                $table->string('module')->nullable()->comment('模块');
                $table->bigInteger('mer_id')->default(0)->comment('商户ID');

                $table->timestamps();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        //删除 reverse
        Schema::dropIfExists($this->table);
    }
};
