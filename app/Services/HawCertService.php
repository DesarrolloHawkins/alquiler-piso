<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HawCertService
{
    protected string $baseUrl = 'https://hawcert.hawkins.es';

    protected string $serviceSlug = 'alquiler-piso';

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Validar certificado por certificate_key + service_slug.
     * Devuelve array con success, access_token, user, certificate, permissions o error.
     */
    public function validateCertificate(string $certificateKey, ?string $serviceSlug = null): array
    {
        $slug = $serviceSlug ?? $this->serviceSlug;
        $url = $this->baseUrl . '/api/validate-certificate';

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($url, [
                'certificate_key' => $certificateKey,
                'service_slug' => $slug,
            ]);

        $body = $response->json();
        $status = $response->status();

        if ($status !== 200) {
            Log::warning('HawCert validate-certificate error', [
                'status' => $status,
                'body' => $body,
            ]);
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Error al validar el certificado',
                'http_status' => $status,
            ];
        }

        if (empty($body['success']) || $body['success'] !== true) {
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Certificado no válido',
                'http_status' => $status,
            ];
        }

        return [
            'success' => true,
            'access_token' => $body['access_token'] ?? null,
            'expires_at' => $body['expires_at'] ?? null,
            'user' => $body['user'] ?? [],
            'certificate' => $body['certificate'] ?? [],
            'permissions' => $body['permissions'] ?? [],
        ];
    }

    /**
     * Validar acceso con certificado PEM y obtener access key (un solo uso).
     * Lo usa el cliente; la plataforma luego consume la key con validateKey().
     */
    public function validateAccess(string $certificatePem, string $url, ?string $serviceSlug = null): array
    {
        $slug = $serviceSlug ?? $this->serviceSlug;
        $endpoint = $this->baseUrl . '/api/validate-access';

        $payload = [
            'certificate' => $certificatePem,
            'url' => $url,
        ];
        if ($slug) {
            $payload['service_slug'] = $slug;
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($endpoint, $payload);

        $body = $response->json();
        $status = $response->status();

        if ($status !== 200) {
            Log::warning('HawCert validate-access error', [
                'status' => $status,
                'body' => $body,
            ]);
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Error al validar el acceso',
                'http_status' => $status,
            ];
        }

        if (empty($body['success']) || $body['success'] !== true) {
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Acceso denegado',
                'http_status' => $status,
            ];
        }

        return [
            'success' => true,
            'access_key' => $body['access_key'] ?? null,
            'expires_at' => $body['expires_at'] ?? null,
            'service' => $body['service'] ?? [],
            'user' => $body['user'] ?? [],
            'certificate' => $body['certificate'] ?? [],
            'permissions' => $body['permissions'] ?? [],
        ];
    }

    /**
     * Validar/consumir la access key (un solo uso).
     * La URL debe tener el mismo host que la usada al generar la key en validate-access.
     */
    public function validateKey(string $key, string $url): array
    {
        $endpoint = $this->baseUrl . '/api/validate-key';

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($endpoint, [
                'key' => $key,
                'url' => $url,
            ]);

        $body = $response->json();
        $status = $response->status();

        if ($status !== 200) {
            Log::warning('HawCert validate-key error', [
                'status' => $status,
                'body' => $body,
            ]);
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Error al validar la clave de acceso',
                'http_status' => $status,
            ];
        }

        if (empty($body['success']) || empty($body['valid'])) {
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Clave inválida o ya utilizada',
                'http_status' => $status,
            ];
        }

        return [
            'success' => true,
            'valid' => true,
            'certificate' => $body['certificate'] ?? [],
            'user' => $body['user'] ?? [],
            'service' => $body['service'] ?? [],
            'permissions' => $body['permissions'] ?? [],
            'expires_at' => $body['expires_at'] ?? null,
        ];
    }

    /**
     * Obtener el identificador efectivo (email) del usuario desde una respuesta de validación.
     */
    public static function getEffectiveEmail(array $result): ?string
    {
        $user = $result['user'] ?? [];
        $cert = $result['certificate'] ?? [];
        return $user['email'] ?? $cert['email'] ?? null;
    }
}
