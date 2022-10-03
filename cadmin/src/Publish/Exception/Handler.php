<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $debug = config('app.debug');
        
        if ($debug != true)
		{
            foreach ($this->dontReport as $cls) {
                if ($e instanceof $cls)
                    return parent::render($request, $e);
            }
            
            if ($e instanceof FatalErrorException || $e instanceof \ErrorException || $e instanceof \BadMethodCallException)
			{
                return response()->view('errors/500', ['e' => $e], 500);
			}
            else if ($e instanceof TokenMismatchException)
            {
                return response()->view('errors/expired', [], 403);
            }
            else if (method_exists($e, 'getStatusCode') && $e->getStatusCode() == 403) 
            {
                return response()->view('errors/403', [], 403);
            }
			else
			{
				return response()->view('errors/404', [], 404);
			}
		}

		return parent::render($request, $e);
    }
}
