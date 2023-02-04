<?php

namespace mradang\LaravelAttachment\Test;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class FeatureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testBasicFeatures()
    {
        $user1 = User::create(['name' => 'user1']);
        $this->assertSame(1, $user1->id);

        // 下载 URL 并上传
        $url = 'https://img.alicdn.com/tfs/TB1_uT8a5ERMeJjSspiXXbZLFXa-143-59.png';
        $att1 = $user1->attachmentAddByUrl($url, [
            'group' => 'photo',
            'name' => 'image1',
            'time' => time(),
        ]);
        $this->assertSame('image1', Arr::get($att1->data, 'name'));
        $this->assertTrue(Storage::disk($this->app['config']['attachment.disk'])->exists($att1->filename));

        // 上传文件
        $width = 240;
        $height = 160;
        $fakeImage = UploadedFile::fake()->image('image2.jpg', $width, $height);
        $att2 = $user1->attachmentAddByFile($fakeImage);
        $this->assertSame($width, $att2->imageInfo['width']);
        $this->assertSame($height, $att2->imageInfo['height']);
        $this->assertSame([], $att2->data);
        $this->assertTrue(Storage::disk($this->app['config']['attachment.disk'])->exists($att2->filename));

        // 现有 2 个附件
        $this->assertSame(2, $user1->attachments->count());

        // 删除第 2 个附件
        $user1->attachmentDelete($att2->id);
        $this->assertNotTrue(Storage::disk($this->app['config']['attachment.disk'])->exists($att2->filename));
        $user1->load('attachments');
        $this->assertSame(1, $user1->attachments->count());

        // 清空所有附件
        $user1->attachmentClear();
        $this->assertNotTrue(Storage::disk($this->app['config']['attachment.disk'])->exists($att1->filename));
        $user1->load('attachments');
        $this->assertSame(0, $user1->attachments->count());

        // 自动删除附件
        $fakeImage = UploadedFile::fake()->image('image3.jpg');
        $att3 = $user1->attachmentAddByFile($fakeImage);
        $this->assertSame([], $att3->data);
        $this->assertEquals($fakeImage->getSize(), $att3->filesize);
        $user1->delete();
        $this->assertNotTrue(Storage::disk($this->app['config']['attachment.disk'])->exists($att3->filename));
    }
}
