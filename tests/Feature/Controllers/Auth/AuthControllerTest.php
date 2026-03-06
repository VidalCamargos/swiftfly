<?php

namespace tests\Feature\Controllers\Auth;

use App\Models\User;
use App\Services\Auth\JwtService;
use Illuminate\Support\Facades\Config;
use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    private User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeSecond();

        $this->user = User::factory()->create();

        Config::set('app.admin_code', 'admin_code_test');
    }

    #[DataProvider('registerBodyProvider')]
    public function test_it_will_register_user_properly(array $requestBody, array $databaseExpected): void
    {
        $jwtMock = $this->mock(JwtService::class);
        $jwtMock->shouldReceive('authenticate')
            ->once()
            ->andReturn([
                'jit' => $this->user->id + 1,
                'expires_at' => now()->addDays(10)->timestamp,
                'token' => 'test',
                'type' => 'bearer',
            ]);

        $this->app->instance(JwtService::class, $jwtMock);

        $this->postJson(route('auth.register', $requestBody))
            ->assertCreated()
            ->assertJson([
                'jit' => $this->user->id + 1,
                'expires_at' => now()->addDays(10)->timestamp,
                'token' => 'test',
                'type' => 'bearer',
            ]);

        $this->assertDatabaseCount(User::class, 2);
        $this->assertDatabaseHas(User::class, $databaseExpected);
    }

    public static function registerBodyProvider(): Iterator
    {
        $defaultRequestBody = [
            'name' => 'Gustavo',
            'email' => 'gustavo@test.com',
            'password' => 'Gu12345678!',
            'password_confirmation' => 'Gu12345678!',
        ];

        yield 'without admin code' => [
            $defaultRequestBody,
            [
                'name' => 'Gustavo',
                'email' => 'gustavo@test.com',
            ],
        ];

        yield 'with admin code' => [
            [
                ...$defaultRequestBody,
                'admin_code' => 'admin_code_test',
            ],
            [
                'name' => 'Gustavo',
                'email' => 'gustavo@test.com',
                'is_admin' => true,
            ],
        ];
    }

    public function test_it_will_login_user_properly(): void
    {
        $jwtMock = $this->mock(JwtService::class);
        $jwtMock->shouldReceive('authenticate')
            ->once()
            ->andReturn([
                'jit' => $this->user->id,
                'expires_at' => now()->addDays(10)->timestamp,
                'token' => 'test',
                'type' => 'bearer',
            ]);

        $this->app->instance(JwtService::class, $jwtMock);

        $this->postJson(route('auth.login', ['email' => $this->user->email, 'password' => 'password']))
            ->assertOk()
            ->assertJson([
                'jit' => $this->user->id,
                'expires_at' => now()->addDays(10)->timestamp,
                'token' => 'test',
                'type' => 'bearer',
            ]);
    }

    public function test_it_will_logout_user_properly(): void
    {
        auth()->login($this->user);

        $this->assertTrue(auth()->check());

        $this->setUserAuthentication($this->user)
            ->postJson(route('auth.logout'))
            ->assertNoContent();

        $this->assertFalse(auth()->check());
    }

    public function test_if_fails_when_email_already_exists(): void
    {
        $this->postJson(route('auth.register', [
            'name' => $this->faker->name,
            'email' => $this->user->email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]))
            ->assertUnprocessable()
            ->assertJsonFragment(['email' => ['O campo email já está sendo utilizado.']]);
    }

    public function test_if_it_fails_when_wrong_password(): void
    {
        $this->postJson(route('auth.login', ['email' => $this->user->email, 'password' => 'invalid']))
            ->assertUnauthorized();
    }

    public function test_if_it_fails_when_email_was_not_found(): void
    {
        $this->postJson(route('auth.login', ['email' => 'invalid@email.com', 'password' => 'password']))
            ->assertUnauthorized();
    }

    #[DataProvider('unprocessableRegisterProvider')]
    public function test_if_fails_when_when_pass_invalid_params_on_register(array $requestBody, array $expectedMessage): void
    {
        $this->postJson(route('auth.register', $requestBody))
            ->assertUnprocessable()
            ->assertJsonFragment($expectedMessage);
    }

    public static function unprocessableRegisterProvider(): Iterator
    {
        yield 'without email' => [
            [
                'name' => 'Gustavo',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            ['email' => ['O campo email é obrigatório.']],
        ];

        yield 'invalid email' => [
            [
                'name' => 'Gustavo',
                'email' => 'invalid-email',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            ['email' => ['O campo email deve ser um endereço de e-mail válido.']],
        ];

        yield 'without name' => [
            [
                'email' => 'test@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            ['name' => ['O campo nome é obrigatório.']],
        ];

        yield 'without password' => [
            [
                'name' => 'Gustavo',
                'email' => 'test@test.com',
            ],
            ['password' => ['O campo senha é obrigatório.']],
        ];

        yield 'invalid password' => [
            [
                'name' => 'Gustavo',
                'email' => 'test@test.com',
                'password' => 'one',
                'password_confirmation' => 'other password',
            ],
            [
                'password' => [
                    'O campo senha de confirmação não confere.',
                    'O campo senha deve conter pelo menos um número.',
                    'O campo senha deve conter pelo menos um símbolo.',
                    'O campo senha deve ter pelo menos 8 caracteres.',
                ],
            ],
        ];

        yield 'invalid password confirmation' => [
            [
                'name' => 'Gustavo',
                'email' => 'test@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'other password',
            ],
            ['password' => ['O campo senha de confirmação não confere.']],
        ];

        yield 'invalid admin code' => [
            [
                'name' => 'Vidal',
                'email' => 'test@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'admin_code' => 'invalid',
            ],
            ['admin_code' => ['O código de administrador está incorreto!']],
        ];
    }
}
