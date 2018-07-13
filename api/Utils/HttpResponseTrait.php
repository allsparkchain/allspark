<?php

namespace App\Utils;


trait HttpResponseTrait
{
    public function respone($status, $message = "", $data = []) {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }
}