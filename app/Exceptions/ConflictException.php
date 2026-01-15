<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTraits;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ConflictException extends BaseException
{
    use ResponseTraits;

    protected $message;
    protected $errors;

    public function __construct($message = "409 Conflict", $errors = []) {
        $this->message = $message;
        $this->errors = $errors;
    }

    public function report()
    {
        Log::debug('Report at ConflictException');
    }

    public function render($request)
    {
        return $this->responseFail(
            Response::HTTP_CONFLICT, // 409
            $this->message,
            $this->errors,
        );
    }
}
