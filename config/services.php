<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dịch Vụ Bên Thứ Ba
    |--------------------------------------------------------------------------
    |
    | File này dùng để lưu trữ thông tin xác thực cho các dịch vụ bên thứ ba
    | như Mailgun, Postmark, AWS và nhiều dịch vụ khác. Đây là nơi quy ước
    | để các package tìm kiếm thông tin xác thực dịch vụ tương ứng.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
