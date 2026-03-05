<?php

namespace Feature\Controllers\TravelOrder;

use App\Enums\TravelOrder\Status;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TravelOrderControllerTest extends TestCase
{
    private User $user;
    private User $admin;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->freezeSecond();
    }

    private function createTravelOrder(User $user, array $attributes = []): TravelOrder
    {
        $travelOrder = TravelOrder::factory()->for($user)->create();

        $travelOrder->fill($attributes);
        $travelOrder->saveQuietly();

        return $travelOrder->refresh();
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson(route('travel-orders.index'))
            ->assertUnauthorized();
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson(route('travel-orders.store', [
            'destination' => 'Paris',
            'departure_date' => '2099-01-01 00:00:00',
        ]))
            ->assertUnauthorized();
    }

    public function test_show_requires_authentication(): void
    {
        $travelOrder = $this->createTravelOrder($this->user);

        $this->getJson(route('travel-orders.show', $travelOrder))
            ->assertUnauthorized();
    }

    public function test_update_requires_authentication(): void
    {
        $travelOrder = $this->createTravelOrder($this->user);

        $this->putJson(route('travel-orders.update', [
            'travelOrder' => $travelOrder,
            'status' => Status::APPROVED->value,
        ]))
            ->assertUnauthorized();
    }

    public function test_it_will_create_travel_order_with_return_date_properly(): void
    {
        $expectedDepartureDate = Carbon::parse('2099-01-01 00:00:00')->toIso8601String();
        $expectedReturnDate = Carbon::parse('2099-01-02 00:00:00')->toIso8601String();

        $this->setUserAuthentication($this->user)
            ->postJson(route('travel-orders.store', [
                'destination' => 'Sao Paulo',
                'departure_date' => '2099-01-01 00:00:00',
                'return_date' => '2099-01-02 00:00:00',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.requester_name', $this->user->name)
            ->assertJsonPath('data.destination', 'Sao Paulo')
            ->assertJsonPath('data.status', Status::REQUESTED->value)
            ->assertJsonPath('data.departure_date', $expectedDepartureDate)
            ->assertJsonPath('data.return_date', $expectedReturnDate)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'requester_name',
                    'destination',
                    'status',
                    'departure_date',
                    'return_date',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas(TravelOrder::class, [
            'user_id' => $this->user->id,
            'requester_name' => $this->user->name,
            'status' => Status::REQUESTED->value,
            'destination' => 'Sao Paulo',
        ]);
    }

    public function test_it_will_create_travel_order_without_return_date_properly(): void
    {
        $expectedDepartureDate = Carbon::parse('2099-01-01 00:00:00')->toIso8601String();

        $this->setUserAuthentication($this->user)
            ->postJson(route('travel-orders.store', [
                'destination' => 'Paris',
                'departure_date' => '2099-01-01 00:00:00',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.requester_name', $this->user->name)
            ->assertJsonPath('data.destination', 'Paris')
            ->assertJsonPath('data.status', Status::REQUESTED->value)
            ->assertJsonPath('data.departure_date', $expectedDepartureDate)
            ->assertJsonPath('data.return_date', null);

        $this->assertDatabaseHas(TravelOrder::class, [
            'user_id' => $this->user->id,
            'requester_name' => $this->user->name,
            'status' => Status::REQUESTED->value,
            'destination' => 'Paris',
        ]);
    }

    #[DataProvider('unprocessableStoreProvider')]
    public function test_if_fails_when_pass_invalid_params_on_store(array $requestBody, array $expectedMessage): void
    {
        $this->setUserAuthentication($this->user)
            ->postJson(route('travel-orders.store', $requestBody))
            ->assertUnprocessable()
            ->assertJsonFragment($expectedMessage);
    }

    public static function unprocessableStoreProvider(): Iterator
    {
        yield 'without destination' => [
            [
                'departure_date' => '2099-01-01 00:00:00',
            ],
            ['destination' => ['O campo destination é obrigatório.']],
        ];

        yield 'destination too short' => [
            [
                'destination' => 'No',
                'departure_date' => '2099-01-01 00:00:00',
            ],
            ['destination' => ['O campo destination deve ter pelo menos 3 caracteres.']],
        ];

        yield 'without departure_date' => [
            [
                'destination' => 'Paris',
            ],
            ['departure_date' => ['O campo departure date é obrigatório.']],
        ];

        yield 'departure_date invalid format' => [
            [
                'destination' => 'Paris',
                'departure_date' => '2026-03-01',
            ],
            [
                'departure_date' => [
                    'O campo departure date deve ser uma data posterior a now.',
                    'O campo departure date não corresponde ao formato Y-m-d H:i:s.',
                ],
            ],
        ];

        yield 'departure_date must be after now' => [
            [
                'destination' => 'Paris',
                'departure_date' => '2000-01-01 00:00:00',
            ],
            ['departure_date' => ['O campo departure date deve ser uma data posterior a now.']],
        ];

        yield 'return_date invalid format' => [
            [
                'destination' => 'Paris',
                'departure_date' => '2099-01-01 00:00:00',
                'return_date' => 'invalid',
            ],
            [
                'return_date' => [
                    'O campo return date deve ser uma data posterior a departure date.',
                    'O campo return date não corresponde ao formato Y-m-d H:i:s.',
                ],
            ],
        ];

        yield 'return_date must be after departure_date' => [
            [
                'destination' => 'Paris',
                'departure_date' => '2099-01-02 00:00:00',
                'return_date' => '2099-01-01 00:00:00',
            ],
            ['return_date' => ['O campo return date deve ser uma data posterior a departure date.']],
        ];
    }

    public function test_it_will_list_only_authenticated_user_travel_orders(): void
    {
        $otherUser = User::factory()->create();

        $older = TravelOrder::factory()->for($this->user)->create();
        $newer = TravelOrder::factory()->for($this->user)->create();
        TravelOrder::factory()->for($otherUser)->create();

        $this->setUserAuthentication($this->user)
            ->getJson(route('travel-orders.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'requester_name',
                        'destination',
                        'status',
                        'departure_date',
                        'return_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    #[DataProvider('indexFilterProvider')]
    public function test_it_filters_and_sorts_on_index(array $queryParams, array $expectedKeys): void
    {
        $approved = $this->createTravelOrder($this->user, [
            'status' => Status::APPROVED,
            'destination' => 'Rio de Janeiro',
            'departure_date' => now()->addDays(10),
        ]);

        $requested = $this->createTravelOrder($this->user, [
            'status' => Status::REQUESTED,
            'destination' => 'Sao Paulo',
            'departure_date' => now()->addDays(11),
        ]);

        $created = [
            'approved' => $approved,
            'requested' => $requested,
        ];

        $expectedIds = array_map(
            static fn (string $key) => $created[$key]->id,
            $expectedKeys
        );

        $this->setUserAuthentication($this->user)
            ->getJson(route('travel-orders.index', $queryParams))
            ->assertOk()
            ->assertJsonPath('data.*.id', $expectedIds);
    }

    public static function indexFilterProvider(): Iterator
    {
        yield 'filter by status' => [
            [
                'filter' => [
                    'status' => Status::REQUESTED->value,
                ],
            ],
            ['requested'],
        ];

        yield 'filter by destination (ILIKE)' => [
            [
                'filter' => [
                    'destination' => 'rio',
                ],
            ],
            ['approved'],
        ];

        yield 'sort by departure_date asc' => [
            [
                'sort' => 'departure_date',
            ],
            ['approved', 'requested'],
        ];

        yield 'sort by departure_date desc' => [
            [
                'sort' => '-departure_date',
            ],
            ['requested', 'approved'],
        ];
    }

    public function test_it_will_show_travel_order_when_owner(): void
    {
        $travelOrder = TravelOrder::factory()->for($this->user)->create();

        $this->setUserAuthentication($this->user)
            ->getJson(route('travel-orders.show', $travelOrder))
            ->assertOk()
            ->assertJsonPath('data.id', $travelOrder->id)
            ->assertJsonPath('data.requester_name', $travelOrder->requester_name)
            ->assertJsonPath('data.destination', $travelOrder->destination)
            ->assertJsonPath('data.status', $travelOrder->status->value);
    }

    public function test_it_forbids_show_when_not_owner(): void
    {
        $otherUser = User::factory()->create();
        $travelOrder = TravelOrder::factory()->for($otherUser)->create();

        $this->setUserAuthentication($this->user)
            ->getJson(route('travel-orders.show', $travelOrder))
            ->assertForbidden();
    }

    public function test_it_updates_status_when_admin(): void
    {
        $travelOrder = TravelOrder::factory()->for($this->user)->create([
            'status' => Status::REQUESTED,
        ]);

        $response = $this->setUserAuthentication($this->admin)
            ->putJson(route('travel-orders.update', [
                'travelOrder' => $travelOrder,
                'status' => Status::APPROVED->value,
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $travelOrder->id)
            ->assertJsonPath('data.status', Status::APPROVED->value);

        $this->assertDatabaseHas(TravelOrder::class, [
            'id' => $travelOrder->id,
            'status' => Status::APPROVED->value,
        ]);
    }

    public function test_it_forbids_update_when_not_admin(): void
    {
        $travelOrder = TravelOrder::factory()->for($this->user)->create([
            'status' => Status::REQUESTED,
        ]);

        $this->setUserAuthentication($this->user)
            ->putJson(route('travel-orders.update', [
                'travelOrder' => $travelOrder,
                'status' => Status::APPROVED->value,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas(TravelOrder::class, [
            'id' => $travelOrder->id,
            'status' => Status::REQUESTED->value,
        ]);
    }

    #[DataProvider('unprocessableUpdateProvider')]
    public function test_it_fails_validation_on_update(Status $currentStatus, string $payloadStatus, array $expectedMessage): void
    {
        $travelOrder = $this->createTravelOrder($this->user, ['status' => $currentStatus]);

        $this->setUserAuthentication($this->admin)
            ->putJson(route('travel-orders.update', [
                'travelOrder' => $travelOrder,
                'status' => $payloadStatus,
            ]))
            ->assertUnprocessable()
            ->assertJsonFragment($expectedMessage);
    }

    public static function unprocessableUpdateProvider(): Iterator
    {
        yield 'invalid status value' => [
            Status::REQUESTED,
            Status::REQUESTED->value,
            ['status' => ['O campo status selecionado é inválido.']],
        ];

        yield 'status prohibited when already approved' => [
            Status::APPROVED,
            Status::CANCELED->value,
            ['status' => ['O campo status é proibido.']],
        ];
    }
}
