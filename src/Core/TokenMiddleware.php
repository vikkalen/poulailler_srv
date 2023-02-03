<?php
namespace App\Core;

use Slim\Psr7\Response;

class TokenMiddleware
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $handler)
    {
        $token = $request->getHeaderLine('X-Thermo-Token');
	if ($token == $this->token) {
	    return $handler->handle($request);
        }
        return (new Response())
            ->withStatus(401);
    }
}
