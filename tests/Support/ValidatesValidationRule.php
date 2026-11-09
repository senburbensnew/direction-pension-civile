<?php

namespace Tests\Support;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

trait ValidatesValidationRule
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertRulePasses(ValidationRule $rule, mixed $value, array $data = []): void
    {
        $message = $this->runRule($rule, $value, $data);

        $this->assertNull($message, $message ?? 'Expected validation to pass.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertRuleFails(ValidationRule $rule, mixed $value, array $data = []): string
    {
        $message = $this->runRule($rule, $value, $data);

        $this->assertNotNull($message, 'Expected validation to fail.');

        return $message;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function runRule(ValidationRule $rule, mixed $value, array $data): ?string
    {
        if ($rule instanceof DataAwareRule) {
            $rule->setData($data);
        }

        $message = null;

        $fail = static function (string $msg) use (&$message): void {
            $message = $msg;
        };

        $rule->validate('attribute', $value, $fail);

        return $message;
    }
}
