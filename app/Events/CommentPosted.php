<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Comment $comment) {}

    public function broadcastOn(): Channel
    {
        return new Channel('comments');
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    public function broadcastWith(): array
     {
     return [
          'id' => $this->comment->id,
          'text' => $this->comment->text,
          'user_name' => $this->comment->user_name,
          'email' => $this->comment->email,
     ];
     }
}
