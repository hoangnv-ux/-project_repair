<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTraits;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends BaseException
{
    use ResponseTraits;

    protected $message;
    protected $errors;

    public function __construct(
        $message = "404 Not Found",
        $errors = [],
    ) {
        $this->message = $message;
        $this->errors = $errors;
    }

    public function report()
    {
        Log::debug('Report at NotFoundException');
    }

    public function render($request)
    {
        return $this->responseFail(
            Response::HTTP_NOT_FOUND,
            $this->message,
            $this->errors,
        );
    }
}
