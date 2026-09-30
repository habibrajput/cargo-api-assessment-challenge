<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PriceApiTest extends TestCase
{
    private const QUOTE = ['Company' => 1, 'Price' => 100, 'Origin' => 'CNSGH', 'Date' => '2020-01-01'];

    public function test_rates_are_an_empty_object_before_any_quote(): void
    {
        $this->assertSame('{}', $this->getJson('/')->assertOk()->getContent());
    }

    public function test_posted_quotes_are_reflected_in_the_rates(): void
    {
        $this->postJson('/', ['Company' => 1, 'Price' => 2000, 'Origin' => 'CNSGH', 'Date' => '2020-01-01'])->assertOk();
        $this->postJson('/', ['Company' => 2, 'Price' => 1500, 'Origin' => 'CNNBO', 'Date' => '2020-01-01'])->assertOk();
        $this->postJson('/', ['Company' => 3, 'Price' => 1700, 'Origin' => 'CNSGH', 'Date' => '2020-01-01'])->assertOk();

        $this->getJson('/')->assertExactJson(['CNSGH' => 1850, 'CNNBO' => 1500]);
    }

    public function test_company_zero_is_accepted_because_the_test_client_sends_it(): void
    {
        $this->postJson('/', ['Company' => 0] + self::QUOTE)->assertOk();
    }

    public static function invalidQuotes(): array
    {
        return [
            'missing field' => [array_diff_key(self::QUOTE, ['Date' => true])],
            'company too high' => [['Company' => 1000] + self::QUOTE],
            'company as string' => [['Company' => '1'] + self::QUOTE],
            'price zero' => [['Price' => 0] + self::QUOTE],
            'price too high' => [['Price' => 100000] + self::QUOTE],
            'unknown origin' => [['Origin' => 'XXXXX'] + self::QUOTE],
            'bad date format' => [['Date' => '01-01-2020'] + self::QUOTE],
        ];
    }

    #[DataProvider('invalidQuotes')]
    public function test_invalid_quotes_are_rejected(array $quote): void
    {
        $this->postJson('/', $quote)->assertUnprocessable();
    }

    public function test_errors_are_json_even_without_an_accept_header(): void
    {
        // The test client sends no Accept header; Laravel would otherwise redirect (302).
        $this->call('POST', '/', server: ['CONTENT_TYPE' => 'application/json'], content: '{"Company":1}')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['Price', 'Origin', 'Date']);
    }
}
