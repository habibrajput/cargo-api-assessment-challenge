<?php

namespace App\Http\Controllers;

use App\Enums\Origin;
use App\Http\Requests\StorePriceRequest;
use App\Services\PriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PriceController extends Controller
{
    public function __construct(private PriceService $prices) {}

    public function store(StorePriceRequest $request): Response
    {
        $quote = $request->validated();

        $this->prices->record(
            company: $quote['Company'],
            origin: Origin::from($quote['Origin']),
            date: $quote['Date'],
            price: $quote['Price'],
        );

        return response('', 200);
    }

    public function index(): JsonResponse
    {
        // Return {} instead of [] when there is no data.
        return response()->json($this->prices->expectedRates(), options: JSON_FORCE_OBJECT);
    }
}
