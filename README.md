# Cargo API

A small API that takes freight price quotes and returns the expected rate for each
port: the average of the 10 cheapest companies, rounded down. Only each company's
latest price counts. The full brief is in [CHALLENGE.md](CHALLENGE.md).

| Request | Result |
|---|---|
| `POST /` with `{"Company":807,"Price":239,"Origin":"CNSGH","Date":"2018-04-10"}` | `200` |
| `GET /` | `{"CNSGH":2615,"SGSIN":3029}` |

## Run and test

Needs Docker. To run the tests locally: PHP 8.3 and Composer.

```sh
cd cargo-api
docker compose up --build   # http://localhost:3142
php artisan test            # after composer install
```

Verified with the supplied test client: 1,000,000 quotes, all checks passed, 0 errors.

## How it works

Request → [`StorePriceRequest`](cargo-api/app/Http/Requests/StorePriceRequest.php) (checks input)
→ [`PriceController`](cargo-api/app/Http/Controllers/PriceController.php)
→ [`PriceService`](cargo-api/app/Services/PriceService.php) (all the logic)

## Key decisions

- **No database.** There are at most 5,000 prices (1,000 companies × 5 ports), so they live in memory.
- **Laravel Octane.** Keeps one PHP process running, so the prices are not lost between requests.
- **One worker.** One shared copy of the prices, and no race conditions.
- **Two required Octane flags** in the [`Dockerfile`](cargo-api/Dockerfile): a fixed admin port
  (the default crashes on port 3142) and a very high request limit (otherwise the worker restarts and loses the prices).
- **Company 0 is allowed.** The brief says 1–999, but the test client sends 0.

## Libraries

- **Laravel**: routing, input validation and testing tools, so the code stays small and familiar.
- **Laravel Octane**: keeps the PHP process alive between requests, which is what lets the prices stay in memory.

## Limitations

- Prices are lost when the app restarts.
- One worker means one CPU core.

## To scale

Move the prices to Redis behind the same `PriceService` methods. Nothing else needs to change.
