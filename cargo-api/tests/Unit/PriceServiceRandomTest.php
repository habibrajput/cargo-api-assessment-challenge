<?php

namespace Tests\Unit;

use App\Enums\Origin;
use App\Services\PriceService;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

/** Checks PriceService against a simple, separate calculation using many random quotes. */
class PriceServiceRandomTest extends TestCase
{
    public function test_random_quotes_match_a_simple_reference_calculation(): void
    {
        $random = new Randomizer(new Mt19937(42)); // fixed seed: same quotes on every run
        $service = new PriceService;
        $quotes = [];

        for ($i = 1; $i <= 5000; $i++) {
            // Few companies and dates, so the same company and date repeat often.
            $company = $random->getInt(0, 30);
            $origin = Origin::cases()[$random->getInt(0, 4)];
            $date = sprintf('2020-%02d-%02d', $random->getInt(1, 12), $random->getInt(1, 28));
            // The brief says a company has one price per port per date, so derive it from those.
            $price = 1 + crc32("$company|$origin->value|$date") % 99999;

            $service->record($company, $origin, $date, $price);
            $quotes[] = [$company, $origin->value, $date, $price];

            if ($i % 250 === 0) {
                $this->assertSame($this->reference($quotes), $service->expectedRates());
            }
        }
    }

    /** Recalculates the rates from all quotes at once, without using PriceService. */
    private function reference(array $quotes): array
    {
        $byDate = [];
        foreach ($quotes as [$company, $origin, $date, $price]) {
            $byDate[$origin][$company][$date] = $price;
        }

        $rates = [];
        foreach ($byDate as $origin => $companies) {
            $prices = [];
            foreach ($companies as $pricesByDate) {
                ksort($pricesByDate);
                $prices[] = end($pricesByDate); // the latest date
            }
            sort($prices);
            $cheapest = array_slice($prices, 0, 10);
            $rates[$origin] = (int) floor(array_sum($cheapest) / count($cheapest));
        }

        return $rates;
    }
}
