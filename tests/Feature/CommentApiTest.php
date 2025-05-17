<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Comment;
use Illuminate\Support\Facades\Event;
use App\Events\CommentPosted;
use Illuminate\Support\Facades\Queue;
use App\Jobs\NotifyAdminOfNewComment;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_comment_tree()
    {
        $response = $this->getJson('/api/comments');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_creates_a_comment_successfully()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test.jpg');

        $payload = [
            'user_name' => 'TestUser',
            'email' => 'test@example.com',
            'text' => '<strong>Test comment</strong>',
            'captcha' => 'ABC123',
            'attachment' => $file,
        ];

        // Переопределяем captcha внутри request, если необходимо
        $this->withSession(['captcha' => 'ABC123']);

        $response = $this->postJson('/api/comments', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'comment' => [
                    'id',
                    'user_name',
                    'email',
                    'text',
                    'attachment_path',
                    'attachment_type',
                    'created_at',
                ],
            ]);

        // Проверка, что файл действительно сохранён
        $savedPath = $response['comment']['attachment_path'];
        Storage::disk('public')->assertExists($savedPath);
    }

    #[Test]
    public function it_returns_validation_error_if_fields_missing()
    {
        $response = $this->postJson('/api/comments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_name', 'email', 'text', 'captcha']);
    }

    #[Test]
    public function it_returns_404_when_downloading_nonexistent_file()
    {
        // Создаём комментарий с фейковым путем
        $comment = Comment::create([
            'user_name' => 'TestUser',
            'email' => 'test@example.com',
            'text' => 'Test',
            'attachment_path' => 'attachments/not-exist.txt',
            'attachment_type' => 'text',
        ]);

        $response = $this->get("/api/comments/{$comment->id}/download");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Файл не найден']);
    }

    #[Test]
     public function it_creates_a_nested_reply_to_comment()
     {
     $parent = Comment::create([
          'user_name' => 'ParentUser',
          'email' => 'parent@example.com',
          'text' => 'Parent comment',
          'captcha' => 'ABC123', // для совместимости
     ]);

     $payload = [
          'user_name' => 'ChildUser',
          'email' => 'child@example.com',
          'text' => 'This is a reply',
          'captcha' => 'ABC123',
          'parent_id' => $parent->id,
     ];

     $this->withSession(['captcha' => 'ABC123']);

     $response = $this->postJson('/api/comments', $payload);

     $response->assertStatus(201)
          ->assertJsonPath('comment.parent_id', $parent->id);
     }

     #[Test]
     public function it_dispatches_broadcast_event_when_comment_created()
     {
     Event::fake([CommentPosted::class]);

     $payload = [
          'user_name' => 'BroadUser',
          'email' => 'broad@example.com',
          'text' => 'Broadcasted!',
          'captcha' => 'ABC123',
     ];

     $this->withSession(['captcha' => 'ABC123']);

     $this->postJson('/api/comments', $payload)->assertStatus(201);

     Event::assertDispatched(CommentPosted::class);
     }

     #[Test]
     public function it_dispatches_queue_job_after_comment_created()
     {
     Queue::fake();

     $payload = [
          'user_name' => 'QueueUser',
          'email' => 'queue@example.com',
          'text' => 'Test queue job',
          'captcha' => 'ABC123',
     ];

     $this->withSession(['captcha' => 'ABC123']);

     $this->postJson('/api/comments', $payload)->assertStatus(201);

     Queue::assertPushed(NotifyAdminOfNewComment::class);
     }
}
