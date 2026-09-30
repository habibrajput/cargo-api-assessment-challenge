<?php

namespace Tests\Unit;

use App\Enums\Origin;
use App\Services\PriceService;
use PHPUnit\Framework\TestCase;

class PriceServiceTest extends TestCase
{
    private PriceService $prices;

    protected function setUp(): void
    {
        $this->prices = new PriceService;
    }

    public function test_it_matches_the_example_from_the_brief(): void
    {
        $this->prices->record(1, Origin::Shanghai, '2020-01-01', 2000);
        $this->prices->record(2, Origin::Ningbo, '2020-01-01', 1500);
        $this->prices->record(3, Origin::Shanghai, '2020-01-01', 1700);

        $this->assertSame(['CNSGH' => 1850, 'CNNBO' => 1500], $this->prices->expectedRates());
    }

    public function test_the_latest_date_wins_even_when_quotes_arrive_out_of_order(): void
    {
        $this->prices->record(123, Origin::Singapore, '2021-01-15', 999);
        $this->prices->record(123, Origin::Singapore, '2021-01-23', 5000);
        $this->prices->record(123, Origin::Singapore, '2021-01-01', 100);

        $this->assertSame(['SGSIN' => 5000], $this->prices->expectedRates());
    }

    public function test_a_repeated_date_keeps_the_first_price(): void
    {
        $this->prices->record(1, Origin::Shanghai, '2020-01-01', 500);
        $this->prices->record(1, Origin::Shanghai, '2020-01-01', 900);

        $this->assertSame(['CNSGH' => 500], $this->prices->expectedRates());
    }

    public function test_only_the_ten_cheapest_companies_count(): void
    {
        foreach (range(1, 10) as $company) {
            $this->prices->record($company, Origin::Shanghai, '2020-01-01', $company);
        }
        $this->prices->record(11, Origin::Shanghai, '2020-01-01', 99999);

        // (1 + 2 + ... + 10) / 10 = 5.5, rounded down
        $this->assertSame(['CNSGH' => 5], $this->prices->expectedRates());
    }

    public function test_fewer_than_ten_companies_are_averaged_and_rounded_down(): void
    {
        $this->prices->record(1, Origin::Guangzhou, '2020-01-01', 5);
        $this->prices->record(2, Origin::Guangzhou, '2020-01-01', 6);

        $this->assertSame(['CNGGZ' => 5], $this->prices->expectedRates());
    }

    public function test_origins_are_calculated_separately(): void
    {
        $this->prices->record(1, Origin::Shanghai, '2020-01-01', 100);
        $this->prices->record(1, Origin::Ningbo, '2020-01-01', 200);

        $this->assertSame(['CNSGH' => 100, 'CNNBO' => 200], $this->prices->expectedRates());
    }

    public function test_no_prices_means_no_rates(): void
    {
        $this->assertSame([], $this->prices->expectedRates());
    }
}
