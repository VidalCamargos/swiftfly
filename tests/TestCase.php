<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
    use WithFaker;

    protected function setUserAuthentication(User $user): self
    {
        $this->withHeader('Authorization', 'Bearer'.auth()->login($user));

        return $this;
    }
}
