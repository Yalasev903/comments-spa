<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Response;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('children')
            ->whereNull('parent_id')
            ->latest()
            ->paginate(25);

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

            // Создаём папку при необходимости
            if (!Storage::disk('public')->exists('attachments')) {
                Storage::disk('public')->makeDirectory('attachments');
                Log::info('Папка attachments создана вручную');
            }

            try {
                // Обработка изображений
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    Log::info('🖼 Обработка загружаемого изображения', [
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
                    // Текстовые файлы
                    Log::info('📎 Прикреплён текстовый файл', ['name' => $fileName]);
                    Storage::disk('public')->putFileAs('attachments', $file, $fileName);
                    $data['attachment_type'] = 'text';
                }

                $data['attachment_path'] = $uploadPath;
                Log::info('Вложение успешно сохранено', ['path' => $uploadPath]);
            } catch (\Throwable $e) {
                Log::error('❗ Ошибка при загрузке вложения: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'error' => 'Во время загрузки файла произошла ошибка'
                ], 500);
            }
        }

        // Сохраняем комментарий
        $comment = Comment::create($data);

        Log::info('Комментарий успешно создан', ['id' => $comment->id]);

        return response()->json([
            'message' => 'Спасибо! Ваш комментарий добавлен.',
            'comment' => $comment,
        ], 201);
    }

    public function downloadAttachment(Comment $comment)
     {
     if (!$comment->attachment_path || !Storage::disk('public')->exists($comment->attachment_path)) {
          return response()->json(['error' => 'Файл не найден'], 404);
     }

     $filePath = storage_path('app/public/' . $comment->attachment_path);
     $fileName = basename($filePath);

     return response()->download($filePath, $fileName, [
          'Content-Type' => $comment->attachment_type === 'image' 
               ? mime_content_type($filePath) 
               : 'text/plain',
          'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
     ]);
     }
}
