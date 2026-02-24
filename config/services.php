<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'channex' => [
        'webhook_url' => env('CHANNEX_WEBHOOK_URL', 'https://tu-dominio.com/webhook-handler'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
    ],

    'hawkins_ai' => [
        'base_url' => env('HAWKINS_AI_URL', 'https://192.168.1.45/chat'),
        'api_key' => env('HAWKINS_AI_API_KEY', 'OllamaAPI_2024_K8mN9pQ2rS5tU7vW3xY6zA1bC4eF8hJ0lM'),
        'model' => env('HAWKINS_AI_MODEL', 'qwen2.5vl:latest'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'ai_translation' => [
        'url' => env('AI_TRANSLATION_URL', 'https://192.168.1.45/chat/chat'),
        'api_key' => env('AI_TRANSLATION_API_KEY', 'OllamaAPI_2024_K8mN9pQ2rS5tU7vW3xY6zA1bC4eF8hJ0lM'),
        'model' => env('AI_TRANSLATION_MODEL', 'gpt-oss:120b-cloud'),
    ],

    /*
     * URL del endpoint externo al que se envían datos de reserva (fecha entrada, salida, código).
     * Si está vacío, el botón "Enviar a plataforma" no realizará la petición.
     */
    'plataforma_reservas_url' => env('PLATAFORMA_RESERVAS_URL', ''),

];
