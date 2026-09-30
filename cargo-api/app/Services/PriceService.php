<?php

namespace App\Services;

use App\Enums\Origin;

/** Keeps the latest price per company and port in memory, and calculates the rates. */
class PriceService
{
    private const CHEAPEST = 10;

    /** @var array<string, array<int, array{date: string, price: int}>> port => company => latest price */
    private array $latest = [];

    public function record(int $company, Origin $origin, string $date, int $price): void
    {
        if ($this->isNewer($company, $origin, $date)) {
            $this->latest[$origin->value][$company] = ['date' => $date, 'price' => $price];
        }
    }

    /** @return array<string, int> port => average of the 10 cheapest, rounded down */
    public function expectedRates(): array
    {
        return array_map(
            fn (array $quotes) => $this->averageOfCheapest(array_column($quotes, 'price')),
            $this->latest,
        );
    }

    private function isNewer(int $company, Origin $origin, string $date): bool
    {
        $current = $this->latest[$origin->value][$company] ?? null;

        // Dates are YYYY-MM-DD, so text comparison works.
        return $current === null || $date > $current['date'];
    }

    /** @param list<int> $prices */
    private function averageOfCheapest(array $prices): int
    {
        sort($prices);
        $cheapest = array_slice($prices, 0, self::CHEAPEST);

        return intdiv(array_sum($cheapest), count($cheapest));
    }
}
