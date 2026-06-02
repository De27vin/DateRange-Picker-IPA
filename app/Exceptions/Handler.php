<?php

namespace App\Exceptions;

use App\Services\UserContextService;
use App\Services\AccountUpdateService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use App\Exceptions\UcpException;
use App\Mail\ExceptionOccured;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array>
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];


    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            Log::error('Symfony Kernel HTTP Exception:', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'IPs' => request()->getClientIps(),
            ]);

            return response(
                $exception->getMessage(),
                $exception->getStatusCode()
            );
        }

        $message = $exception->getMessage();
        switch (true) {
            case str_contains($message, 'CSRF token mismatch'):
            case str_contains($message, 'Unauthenticated'):
            case str_contains($message, 'Authentication user provider [custom_user_provider] is not defined'):
                app(UserContextService::class)->logoutActiveUser();
                return redirect('/');

            case str_contains($message, 'Undefined array key'):
            case str_contains($message, 'null'):
                return $this->synchronizeErrorAction($request, $exception);

            default:
                return $this->defaultAction($request, $exception);
        }
    }


    private function synchronizeErrorAction($request, $exception)
    {
        if (!Auth::check()) {
            \Log::error($exception, ['Synchro error caught on unauthenticated user']);
            return $this->defaultAction($request, $exception);
        }

        if (session()->has('synchro_error_caught')) {
            \Log::error($exception, ['Synchro error caught twice and refused']);
            return $this->defaultAction($request, $exception);
        }

        \Log::error($exception, ['Synchro error caught']);

        try {
            foreach (app(UserContextService::class)->getUserAccounts() as $account) {
                (new AccountUpdateService($account))->synchronizeAccountJsonSchema();
            }
        } catch (\Throwable $e) {
            \Log::error($e, ['Synchro error caught but failed to perform']);
            return $this->defaultAction($request, $exception);
        }
        /** @var \Illuminate\Http\Request $request */

        \Log::info('Synchro error caught and performed');
        return redirect()->back()->with('synchro_error_caught', true);
    }

    private function defaultAction($request, $exception)
    {
        Log::error($exception, ['Default error handler action performed']);

        // Don't redirect if it's an API or non-HTML request
        if (!$request->acceptsHtml()) {
            return response()->json(['error' => 'Server Error'], 500);
        }

        $currentUrl = url()->current();
        $previousUrl = url()->previous();

        // Only redirect back if:
        // 1. We're not in a redirect loop
        // 2. We're not on a login page
        // 3. It's not a Livewire request
        // 4. We haven't already handled an error
        if ($currentUrl !== $previousUrl &&
            !str_contains($currentUrl.$previousUrl, 'login') &&
            !Str::contains($currentUrl, 'livewire') &&
            !session()->has('global-handler-error')) {

            Log::notice('Redirected back from '.$currentUrl.' to '.$previousUrl);
            return redirect()->back()->with('global-handler-error', true);
        }

        return response()->view('errors.ucp-error', [
            'message' => $this->toString($exception),
        ], 500);
    }


    public function toString($exception)
    {
       if (get_class($exception) === NotFoundHttpException::class) {
           return __('Http Not Found');
       }
        return '';
    }


    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendEmail(Throwable $exception)
    {
        try {
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();

            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();

            Mail::to('tanzania@mac.com')->send(new ExceptionOccured($content));

        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }


}