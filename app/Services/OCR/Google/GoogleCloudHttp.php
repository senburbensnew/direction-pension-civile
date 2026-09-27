<?php

namespace App\Services\OCR\Google;

use App\Services\OCR\Exceptions\OcrException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoogleCloudHttp
{
    public static function client(): PendingRequest
    {
        $request = Http::timeout((int) config('ocr.google.timeout', 60));
        $verify = self::verifyOption();

        if ($verify !== true) {
            $request = $request->withOptions(['verify' => $verify]);
        }

        return $request;
    }

    public static function wrap(callable $callback)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            throw new OcrException(self::friendlyMessage($e), 0, $e);
        }
    }

    /**
     * @return bool|string
     */
    private static function verifyOption(): bool|string
    {
        if (! filter_var(config('ocr.google.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        $bundle = config('ocr.google.ca_bundle');
        if (is_string($bundle) && $bundle !== '' && is_file($bundle)) {
            return $bundle;
        }

        $iniBundle = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
        if (is_string($iniBundle) && $iniBundle !== '' && is_file($iniBundle)) {
            return $iniBundle;
        }

        $fallback = storage_path('certs/cacert.pem');

        return is_file($fallback) ? $fallback : true;
    }

    private static function friendlyMessage(Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'SSL certificate') || str_contains($message, 'error 60')) {
            return 'Certificat SSL introuvable (curl error 60). Placez un fichier CA dans storage/certs/cacert.pem ou définissez curl.cainfo dans php.ini.';
        }

        return 'Impossible de contacter Google Cloud : '.$message;
    }
}
