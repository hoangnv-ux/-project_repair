<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTraits;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends BaseException
{
    use ResponseTraits;

    protected $message;
    protected $errors;

    public function __construct(string $message = "403 Forbidden", array $errors = [])
    {
        parent::__construct($message);
        $this->message = $message;
        $this->errors = $errors;
    }

    public function report()
    {
        Log::warning('ForbiddenException: ' . $this->message);
    }

    public function render($request)
    {
        return $this->responseFail(
            Response::HTTP_FORBIDDEN,
            $this->message,
            $this->errors
        );
    }
}
