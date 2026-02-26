<?php

namespace Tests\Feature;

use App\Services\DummyTimeseriesGenerator;
use Carbon\CarbonImmutable;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    public function test_api_404_returns_json_with_request_id(): void
    {
        $requestId = (string) \Illuminate\Support\Str::uuid();

        $response = $this->getJson('/api/does-not-exist', [
            'X-Request-Id' => $requestId,
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure(['message', 'request_id'])
            ->assertJsonPath('request_id', $requestId);
    }

    public function test_api_405_returns_json_with_request_id(): void
    {
        $requestId = (string) \Illuminate\Support\Str::uuid();

        $response = $this->postJson('/api/timeseries', [], [
            'X-Request-Id' => $requestId,
        ]);

        $response->assertStatus(405)
            ->assertJsonStructure(['message', 'request_id'])
            ->assertJsonPath('request_id', $requestId);
    }

    public function test_api_500_returns_json_with_request_id(): void
    {
        $requestId = (string) \Illuminate\Support\Str::uuid();

        $failingGenerator = new class extends DummyTimeseriesGenerator {
            public function generate(string $chart, CarbonImmutable $startDay, CarbonImmutable $endDayInclusive): array
            {
                throw new RuntimeException('forced failure');
            }
        };

        $this->app->instance(DummyTimeseriesGenerator::class, $failingGenerator);

        $response = $this->getJson(
            '/api/timeseries?chart=EquipmentChart&start=2026-01-24T00:00:00Z&end=2026-01-24T23:00:00Z',
            ['X-Request-Id' => $requestId]
        );

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Internal server error',
                'request_id' => $requestId,
            ]);
    }

    public function test_validation_error_keeps_standard_laravel_422_format(): void
    {
        $response = $this->getJson('/api/timeseries?chart=InvalidChart&start=2026-01-24T00:00:00Z&end=2026-01-24T23:00:00Z');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['chart'],
            ]);
    }
}
