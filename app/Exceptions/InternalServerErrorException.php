<?php

namespace App\Exceptions;

use App\Http\Traits\ResponseTraits;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InternalServerErrorException extends BaseException
{
    use ResponseTraits;

    protected $message;
    protected $errors;

    public function __construct(
        $message = "500 Internal Server Error",
        $errors = [],
    ) {
        $this->message = $message;
        $this->errors = $errors;
    }

    public function report()
    {
        Log::error('Report at InternalServerErrorException', [
            'message' => $this->message,
            'errors'  => $this->errors,
        ]);
    }

    public function render($request)
    {
        return $this->responseFail(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->message,
            $this->errors,
        );
    }
}
