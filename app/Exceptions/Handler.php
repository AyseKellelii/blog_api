<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Eğer debug açık ise Laravel'in kendi hata sayfasını göster
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        // Policy / Gate yetki hataları (403)
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'errors' => [
                    'status' => 403,
                    'title' => 'Bu işlem için yetkiniz yok.'
                ]
            ], 403);
        }

        // Model bulunamadı (404)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'errors' => [
                    'status' => 404,
                    'title' => 'Kayıt bulunamadı.'
                ]
            ], 404);
        }

        // Doğrulama hataları (400)
        if ($e instanceof ValidationException) {
            return response()->json([
                'errors' => [
                    'status' => 400,
                    'title' => 'Geçersiz veri gönderildi.',
                    'details' => $e->errors()
                ]
            ], 400);
        }

        // Genel HTTP hataları (400, 401, 403, 404, 500 vb)
        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'errors' => [
                    'status' => $e->getStatusCode(),
                    'title' => $e->getMessage() ?: 'Bir hata oluştu.'
                ]
            ], $e->getStatusCode());
        }

        // Geri kalan beklenmedik hatalar
        return response()->json([
            'errors' => [
                'status' => 500,
                'title' => 'Sunucu hatası meydana geldi.',
                'message' => $e->getMessage()
            ]
        ], 500);
    }
}
