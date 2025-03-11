<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->id();
            $table->string('attachmentable_type'); // 对应所属模型的类名
            $table->unsignedInteger('attachmentable_id'); // 对应所属模型的 ID
            $table->string('filename'); // 文件名
            $table->unsignedInteger('filesize'); // 文件大小
            $table->string('imageInfo')->nullable(); // 图片信息 JSON（height, width)
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
};
