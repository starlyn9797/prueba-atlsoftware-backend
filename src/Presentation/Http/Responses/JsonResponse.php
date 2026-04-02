<?php

declare(strict_types=1);

namespace App\Presentation\Http\Responses;

final class JsonResponse
{
    public static function success(mixed $data, int $status = 200): void
    {
        self::send(['success' => true, 'data' => $data], $status);
    }

    public static function created(mixed $data): void
    {
        self::success($data, 201);
    }

    public static function error(string $message, int $status = 400, ?array $details = null): void
    {
        $body = ['success' => false, 'error' => $message];

        if ($details !== null) {
            $body['details'] = $details;
        }

        self::send($body, $status);
    }

    private static function send(array $body, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
