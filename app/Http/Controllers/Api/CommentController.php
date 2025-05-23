<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Response;
use App\Jobs\NotifyAdminOfNewComment;
use App\Events\CommentCreated;
use App\Events\CommentPosted;

class CommentController extends Controller
{
     public function index(Request $request)
     {
     $page = (int) $request->get('page', 1);
     $perPage = 25;

     // Новое: сортировка с валидацией
     $sortBy = $request->get('sort_by', 'created_at');
     $order = $request->get('order', 'desc');
     $allowedSorts = ['created_at', 'user_name', 'email'];
     $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
     $order = in_array($order, ['asc', 'desc']) ? $order : 'desc';

     $cacheKey = "comments_tree_{$sortBy}_{$order}_page_{$page}_{$perPage}";

     $comments = Cache::remember($cacheKey, 60, function () use ($perPage, $sortBy, $order) {
          return \App\Models\Comment::with('children')
               ->whereNull('parent_id')
               ->orderBy($sortBy, $order)
               ->paginate($perPage);
     });

     return response()->json($comments);
     }


    public function store(Request $request)
    {
        Log::info('Получен запрос на создание комментария', $request->all());

        $rules = [
            'user_name'  => ['required', 'regex:/^[a-zA-Z0-9]+$/'],
            'email'      => 'required|email',
            'home_page'  => 'nullable|url',
            'text'       => 'required|string',
            'captcha'    => 'required|string',
            'parent_id'  => 'nullable|exists:comments,id',
            'attachment' => 'nullable|file|max:1024|mimes:jpg,jpeg,png,gif,txt',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Валидация не прошла', $validator->errors()->toArray());
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['text'] = strip_tags($data['text'], '<a><strong><i><code>');

        // Обработка вложений
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = uniqid('comment_', true) . '.' . $ext;
            $uploadPath = 'attachments/' . $fileName;

            if (!Storage::disk('public')->exists('attachments')) {
                Storage::disk('public')->makeDirectory('attachments');
                Log::info('Папка attachments создана вручную');
            }

            try {
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    Log::info('Обработка изображения', [
                        'type' => $ext,
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                    ]);

                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file)->resize(320, 240, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    Storage::disk('public')->put($uploadPath, (string) $image->encode());
                    $data['attachment_type'] = 'image';
                } else {
                    Log::info('Прикреплён текстовый файл', ['name' => $fileName]);
                    Storage::disk('public')->putFileAs('attachments', $file, $fileName);
                    $data['attachment_type'] = 'text';
                }

                $data['attachment_path'] = $uploadPath;
                Log::info('Вложение сохранено', ['path' => $uploadPath]);
            } catch (\Throwable $e) {
                Log::error('Ошибка при загрузке вложения: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return response()->json([
                    'error' => 'Во время загрузки файла произошла ошибка'
                ], 500);
            }
        }

        $comment = Comment::create($data);

        // Сброс кэша всех страниц
        $this->clearCommentsCache();

        event(new CommentCreated($comment));
        broadcast(new CommentPosted($comment))->toOthers();

        dispatch(new NotifyAdminOfNewComment($comment));
        Log::info('Комментарий создан', ['id' => $comment->id]);

        return response()->json([
            'message' => 'Спасибо! Ваш комментарий добавлен.',
            'comment' => $comment,
        ], 201);
    }

    // Сброс кэша пагинации комментариев (простая версия — flush, можно оптимизировать)
    private function clearCommentsCache()
    {
        Cache::flush();
        // Или, если нужно только комментарии:
        // foreach (range(1, 20) as $page) { // 20 страниц — запасом
        //     Cache::forget("comments_tree_page_{$page}_5");
        // }
    }

    public function downloadAttachment(Comment $comment)
    {
        Log::info('Запрос на скачивание файла', ['comment_id' => $comment->id]);

        if (!$comment->attachment_path || !Storage::disk('public')->exists($comment->attachment_path)) {
            Log::warning('Файл не найден', ['path' => $comment->attachment_path]);
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        $filePath = storage_path('app/public/' . $comment->attachment_path);
        $fileName = basename($filePath);
        $mimeType = mime_content_type($filePath);

        Log::info('Скачивание файла', ['mime' => $mimeType]);

        return response()->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
