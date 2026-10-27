<?php

namespace Drakakisgeo\Billit\Tests;

use Drakakisgeo\Billit\Billit;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

trait MocksHttp
{
    /** @var array<int, array{request: RequestInterface}> */
    private array $history = [];

    /**
     * A Billit client whose HTTP calls are answered by the given responses, in order.
     */
    private function billit(Response|\Throwable ...$responses): Billit
    {
        $stack = HandlerStack::create(new MockHandler($responses ?: [$this->json([])]));
        $stack->push(Middleware::history($this->history));

        return new Billit('test-token', true, 'v1', new Client([
            'handler' => $stack,
            'base_uri' => Billit::baseUri(true),
        ]));
    }

    private function json(array $body, int $status = 200, array $headers = []): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'] + $headers, json_encode($body));
    }

    private function lastRequest(): RequestInterface
    {
        return end($this->history)['request'];
    }

    private function lastJsonBody(): mixed
    {
        return json_decode((string) $this->lastRequest()->getBody(), true);
    }
}
