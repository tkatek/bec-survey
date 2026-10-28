<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseAnonymousSurveySession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('/') && ! $request->is('conversation-room*')) {
            return $next($request);
        }

        $defaultDriver = config('session.driver');
        $defaultCookie = config('session.cookie');

        config([
            'session.driver' => config('survey.public_session_driver', 'cookie'),
            'session.cookie' => config('survey.public_session_cookie', 'bec-survey-anonymous'),
        ]);

        try {
            return $next($request);
        } finally {
            config([
                'session.driver' => $defaultDriver,
                'session.cookie' => $defaultCookie,
            ]);
        }
    }
}
