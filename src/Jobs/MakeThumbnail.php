<?php

namespace mradang\LaravelAttachment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use mradang\LaravelAttachment\Services\FileService;

class MakeThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file_storage;
    protected $width;
    protected $height;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $file_storage, int $width, int $height)
    {
        $this->file_storage = $file_storage;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FileService::makeThumb($this->file_storage, $this->width, $this->height);
    }
}
