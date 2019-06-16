<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 附件表
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attachmentable_type'); // 对应所属模型的类名
            $table->unsignedInteger('attachmentable_id'); // 对应所属模型的 ID
            $table->string('file_name'); // 文件名
            $table->unsignedInteger('file_size'); // 文件大小
            $table->unsignedInteger('sort'); // 排序
            $table->longText('data'); // 附加数据
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
