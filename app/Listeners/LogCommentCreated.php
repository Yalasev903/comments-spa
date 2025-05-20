<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use Illuminate\Support\Facades\Log;

class LogCommentCreated
{
    public function handle(CommentCreated $event): void
    {
        Log::info("Событие: создан комментарий ID {$event->comment->id}");
    }
}
