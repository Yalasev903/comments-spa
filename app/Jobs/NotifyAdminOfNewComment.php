<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfNewComment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Comment $comment) {}

    public function handle(): void
    {
        Log::info("Новый комментарий от {$this->comment->user_name}");
    }
}

