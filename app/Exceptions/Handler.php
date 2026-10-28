<?php

namespace App\Exceptions;

use Throwable;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception
     * @return mixed|void
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        if (env('APP_ENV') != 'local') {
            if (app()->bound('sentry') && $this->shouldReport($exception)) {
                app('sentry')->captureException($exception);
            }
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }

    /**
     * Staff hitting a 403, 404 or 410 under /staff get the error in the staff layout, with links
     * back into the staff area, instead of the public error page.
     *
     * Only under /staff: public routes have no session, so the user isn't known there, and their
     * responses are edge-cached - a staff error page must never be cached for visitors.
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $request = request();
        $user = $request->user();
        $status = $e->getStatusCode();

        if (isset(self::STAFF_ERRORS[$status]) && $user && ($user->isStaff() || $user->isOwner())
            && $request->is('staff', 'staff/*') && !$request->expectsJson()) {
            [$title, $message] = self::STAFF_ERRORS[$status];
            $bindings = app(StaffPageBuilder::class)
                ->build($title, StaffBreadcrumbs::staffGenericTopLevel($title))->bindings;
            $bindings['ErrorMessage'] = $message;
            $bindings['RequestedUrl'] = $request->fullUrl();

            return response()->view('errors.staff', $bindings, $status, $e->getHeaders());
        }

        return parent::renderHttpException($e);
    }

    const STAFF_ERRORS = [
        403 => ['Forbidden', "Your staff roles don't cover this page."],
        404 => ['Page not found', "There's no staff page at this address, or the record it points to doesn't exist."],
        410 => ['Removed', 'This has been removed.'],
    ];

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
