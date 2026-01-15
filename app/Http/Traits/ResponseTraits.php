<?php

namespace App\Http\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ResponseTraits
{
    public function responseSuccess($status = Response::HTTP_OK, $message = '', $data = [], $meta = null, $view = null)
    {
        if (request()->expectsJson()) {
            $response = [
                'status' => true,
                'message' => $message,
                'data' => $data,
            ];
            if (!is_null($meta)) {
                $response['meta'] = $meta;
            }
            return response()->json($response, $status);
        }

        if ($view) {
            return view($view, compact('message', 'data'));
        }
        return back()->with('success', $message)->with('data', $data);
    }

    public function responseFail($status = Response::HTTP_BAD_REQUEST, $message = '', $errors = [], $view = null)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        }

        if ($view) {
            return view($view, compact('message', 'errors'));
        }
        return back()->withErrors($errors)->with('error', $message)->withInput();
    }
}
