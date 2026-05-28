<?php

namespace App\Exceptions;

use Illuminate\{
    Auth\AuthenticationException,
    Foundation\Exceptions\Handler as ExceptionHandler
};
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Mail;
use App\Mail\TooManyAttemptsMail;

use Exception;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {

        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {

            $email = $request->email;

            if ($email) {

                \Mail::to($email)->send(

                    new \App\Mail\TooManyAttemptsMail($email)

                );

            }

            return back()->withErrors([

                'email' => 'Too many login attempts. A security alert has been sent to your email.'

            ]);

        }

        return parent::render($request, $exception);

    }
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->guest('/admin/login');
        }
        if ($request->is('user') || $request->is('user/*')) {
            return redirect()->guest('/user/login');
        }
        return redirect()->guest(route('user.login'));
    }

}
