<?php

namespace App\Exceptions;

use App\Services\AccountUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use App\Exceptions\UcpException;
use App\Mail\ExceptionOccured;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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
        if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }

        if ($this->isApiRequest($request)) {
            return $this->renderApiException($request, $exception);
        }

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
                Cookie::queue(Cookie::forget('ucp_account'));
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect('/');

            case str_contains($message, 'Undefined array key'):
            case str_contains($message, 'null'):
                return $this->synchronizeErrorAction($request, $exception);

            default:
                return $this->defaultAction($request, $exception);
        }
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function renderApiException(Request $request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return parent::render($request, $exception);
        }

        $requestId = $request->attributes->get('request_id') ?: $request->header('X-Request-Id');

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $message = SymfonyResponse::$statusTexts[$status] ?? 'HTTP error';

            return response()->json([
                'message' => $message,
                'request_id' => $requestId,
            ], $status);
        }

        $this->logApiException($request, $exception, 500);

        return response()->json([
            'message' => 'Internal server error',
            'request_id' => $requestId,
        ], 500);
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
            foreach (Auth::user()->accounts as $account) {
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
        $this->logApiException($request, $exception, 500);

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

    private function logApiException(Request $request, Throwable $exception, int $status): void
    {
        $requestId = $request->attributes->get('request_id') ?: $request->header('X-Request-Id');
        Log::channel('ipa')->error('api.exception', [
            'event' => 'api.exception',
            'request_id' => $requestId,
            'path' => $request->getPathInfo(),
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'status' => $status,
        ]);
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
