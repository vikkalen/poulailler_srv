<?php
namespace App;

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
    public function __invoke($request, $response, $next)
    {
        $token = $request->getHeaderLine('X-Thermo-Token');
        if ($token == $this->token) {
            return $next(
                $request,
                $response
            );
        }
        return $response
            ->withStatus(401);
    }
}