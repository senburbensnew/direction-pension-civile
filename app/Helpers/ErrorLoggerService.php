<?php

// app/Helpers/ErrorLoggerService.php
namespace App\Helpers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ErrorLog;

class ErrorLoggerService
{
    public static function logError(Exception $e, Request $request): void
    {
        try {
            ErrorLog::create([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => json_encode($request->except(['password', 'token'])),
                'user_id' => auth()->check() ? auth()->id() : null
            ]);
        } catch (Exception $loggingException) {
            \Log::error('Failed to log error to database: ' . $loggingException->getMessage());
        }
    }
}

