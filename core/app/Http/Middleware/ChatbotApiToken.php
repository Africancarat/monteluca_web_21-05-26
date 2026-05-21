<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ChatbotApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('chatbot.api_token');

        if (!$expected || $request->header('X-Chatbot-Token') !== $expected) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}