<?php

namespace App\Services\Newsletter;

class NewsletterConfirmResult
{
    public function __construct(
        public readonly string $status,
        public readonly string $message,
    ) {}

    public static function success(string $message): self
    {
        return new self('success', $message);
    }

    public static function invalid(string $message): self
    {
        return new self('invalid', $message);
    }

    public static function expired(string $message): self
    {
        return new self('expired', $message);
    }

    public static function blocked(string $message): self
    {
        return new self('blocked', $message);
    }
}
