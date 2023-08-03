<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function report(Throwable $exception)
    {
/*        $token = '5047124778:AAF3C-WnV5DE63_Zl7itIbDPFweZqadalcI';
        $message = "*** APEX SUG'URTA ***" . "\n" .
            "Exception: " . $exception->getMessage() . "\n" .
            "File: " . $exception->getFile() . "\n".
            "Line: " . $exception->getLine() . "\n";
        $my = ['text' => $message, 'chat_id' => '642217520'];
        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($my));*/

        parent::report($exception);
    }

}
