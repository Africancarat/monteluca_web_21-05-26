<?php

namespace App\Http\Middleware;

use App\Models\ApiAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiAuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'renew_password',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'api_key',
        'secret',
        'email_pass',
        'captcha',
        'g-recaptcha-response',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $statusCode = null;

        try {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $next($request);
            $statusCode = $response->getStatusCode();

            return $response;
        } catch (\Throwable $e) {
            $statusCode = 500;

            throw $e;
        } finally {
            $this->recordAuditLog($request, $statusCode, $startedAt);
        }
    }

    private function recordAuditLog(Request $request, ?int $statusCode, float $startedAt): void
    {
        try {
            ApiAuditLog::create([
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'actor_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'status_code' => $statusCode,
                'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'request_hash' => $this->requestHash($request),
                'at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit logging must never take down APIs or leak sensitive request data.
            Log::warning('API audit logging failed', [
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function requestHash(Request $request): ?string
    {
        $payload = array_merge($request->query(), $request->request->all());

        if ($payload === []) {
            return null;
        }

        $sanitized = $this->redactSensitiveValues($payload);
        $sanitized = $this->sortPayload($sanitized);

        return hash('sha256', json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function redactSensitiveValues(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $payload[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $payload[$key] = $this->redactSensitiveValues($value);
            }
        }

        return $payload;
    }

    private function sortPayload(array $payload): array
    {
        ksort($payload);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sortPayload($value);
            }
        }

        return $payload;
    }
}
