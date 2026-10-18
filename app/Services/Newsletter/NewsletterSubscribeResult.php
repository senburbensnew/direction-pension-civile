<?php

namespace App\Services\Newsletter;

enum NewsletterSubscribeResultType
{
    case Subscribed;
    case Pending;
    case Duplicate;
    case Invalid;
    case Blocked;
    case ResubscribeRequired;
}

class NewsletterSubscribeResult
{
    public function __construct(
        public readonly NewsletterSubscribeResultType $type,
        public readonly string $message,
    ) {}

    public static function subscribed(string $message): self
    {
        return new self(NewsletterSubscribeResultType::Subscribed, $message);
    }

    public static function pending(string $message): self
    {
        return new self(NewsletterSubscribeResultType::Pending, $message);
    }

    public static function duplicate(string $message): self
    {
        return new self(NewsletterSubscribeResultType::Duplicate, $message);
    }

    public static function invalid(string $message): self
    {
        return new self(NewsletterSubscribeResultType::Invalid, $message);
    }

    public static function blocked(string $message): self
    {
        return new self(NewsletterSubscribeResultType::Blocked, $message);
    }

    public static function resubscribeRequired(string $message): self
    {
        return new self(NewsletterSubscribeResultType::ResubscribeRequired, $message);
    }

    public function flashKey(): string
    {
        return match ($this->type) {
            NewsletterSubscribeResultType::Invalid,
            NewsletterSubscribeResultType::Blocked,
            NewsletterSubscribeResultType::Duplicate => 'error',
            default => 'success',
        };
    }
}
