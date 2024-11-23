<?php

namespace App\Helpers;

class BaseResponse{
    public bool $success;
    public string $message;
    public $data;

    public function __construct(
        bool $success = true,
        string $message = "success",
        $data = null
    )
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

    public function toArray(): array{
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data
        ];
    }
}

?>