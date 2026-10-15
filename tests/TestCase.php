<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use Concerns\CreatesPensionneUsers;
    use CreatesApplication;

    /**
     * @return array<string, int>
     */
    protected function passwordConfirmedSession(): array
    {
        return ['auth.password_confirmed_at' => time()];
    }

    protected function actingAsConfirmed(Authenticatable $user): static
    {
        return $this->actingAs($user)->withSession($this->passwordConfirmedSession());
    }
}
