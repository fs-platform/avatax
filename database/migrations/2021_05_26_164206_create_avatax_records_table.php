<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvataxRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('avatax_records')) {
            Schema::create('avatax_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable(false)->comment('用户id');
                $table->string('document_id')->nullable(false)->comment('订单id');
                $table->string('address', 500)->nullable(false)->comment('订单地址');
                $table->string('from', 500)->nullable(false)->comment('发货地址');
                $table->string('order',500)->nullable(false)->comment('订单信息');
                $table->text('lines')->nullable(false)->comment('行信息');
                $table->unsignedTinyInteger('status')->nullable(false)->default(0)->comment('状态 默认为0 0表示失败 1表示成功');
                $table->text('response')->nullable(false)->comment('接口响应数据');
                $table->timestamp('created_at')->nullable(false)->comment('请求时间');

                $table->index(['document_id'], 'index_document_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avatax_records');
    }
}
