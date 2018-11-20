<?php

namespace Tests\Functional;

class GraphTest extends BaseTestCase
{
    protected function mockDI($container)
    {
        $container['token_guard'] = function ($c) {
            return function ($request, $response, $next) {
                return $next($request, $response);
            };
        };
        
    }
    
    public function testGetGraph100x100()
    {
        $response = $this->runApp('GET', '/graph/voltage/daily/?width=100&height=100');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetGraph300x300()
    {
        $response = $this->runApp('GET', '/graph/voltage/daily/?width=300&height=300');

        $this->assertEquals(200, $response->getStatusCode());
    }
}