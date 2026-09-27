<?php

namespace App\Services\OCR\Google;

use App\Services\OCR\Exceptions\OcrException;

class GoogleCloudAuthenticator
{
    /**
     * Build query/header auth for Google Cloud APIs.
     *
     * @return array{query: array<string, string>, headers: array<string, string>}
     */
    public function credentials(): array
    {
        $apiKey = config('ocr.google.api_key');
        if (is_string($apiKey) && $apiKey !== '') {
            return [
                'query' => ['key' => $apiKey],
                'headers' => [],
            ];
        }

        $token = config('ocr.google.access_token') ?: $this->serviceAccountAccessToken();

        return [
            'query' => [],
            'headers' => ['Authorization' => 'Bearer '.$token],
        ];
    }

    private function serviceAccountAccessToken(): string
    {
        $path = config('ocr.google.credentials');
        if (is_string($path) && $path !== '' && ! is_file($path)) {
            $path = base_path($path);
        }
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new OcrException(
                'Authentification Google OCR manquante. Définissez OCR_GOOGLE_API_KEY ou OCR_GOOGLE_CREDENTIALS.'
            );
        }

        $credentials = json_decode((string) file_get_contents($path), true);
        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new OcrException('Le fichier de compte de service Google OCR est invalide.');
        }

        $now = time();
        $assertion = $this->jwt($credentials['client_email'], $credentials['private_key'], $now);

        $response = GoogleCloudHttp::wrap(fn () => GoogleCloudHttp::client()
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]));

        if (! $response->successful() || ! $response->json('access_token')) {
            throw new OcrException(
                $response->json('error_description')
                    ?? $response->json('error')
                    ?? 'Impossible d\'obtenir un jeton Google Cloud.'
            );
        }

        return (string) $response->json('access_token');
    }

    private function jwt(string $clientEmail, string $privateKey, int $now): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $ok = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $ok || ! is_string($signature)) {
            throw new OcrException('Impossible de signer le jeton du compte de service Google.');
        }

        return $unsigned.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
