<?php

declare(strict_types=1);

namespace Jellyfish\HttpAuthentication;

use Codeception\Test\Unit;
use Jellyfish\Http\HttpConstants;
use Jellyfish\Http\HttpFacadeInterface;
use Jellyfish\HttpAuthentication\Middleware\AuthenticationMiddleware;
use Pimple\Container;
use Psr\Http\Server\MiddlewareInterface;

class HttpAuthenticationServiceProviderTest extends Unit
{
    /**
     * @var \Jellyfish\Http\HttpFacadeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpFacadeMock;

    /**
     * @var \Pimple\Container
     */
    protected Container $container;

    /**
     * @var \Jellyfish\HttpAuthentication\HttpAuthenticationServiceProvider
     */
    protected HttpAuthenticationServiceProvider $httpAuthenticationServiceProvider;

    /**
     * @return void
     */
    protected function _before(): void
    {
        parent::_before();

        $this->container = new Container();

        $this->httpFacadeMock = $this->getMockBuilder(HttpFacadeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->offsetSet('app_dir', static fn () => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);

        $self = $this;

        $this->container->offsetSet(HttpConstants::FACADE, static fn () => $self->httpFacadeMock);

        $this->httpAuthenticationServiceProvider = new HttpAuthenticationServiceProvider();
    }

    /**
     * @return void
     */
    public function testRegister(): void
    {
        $this->httpFacadeMock->expects(static::atLeastOnce())
            ->method('addMiddleware')
            ->with(
                static::callback(
                    static fn (MiddlewareInterface $middleware) => $middleware instanceof AuthenticationMiddleware
                )
            )->willReturn($this->httpFacadeMock);

        $this->httpAuthenticationServiceProvider->register($this->container);

        static::assertInstanceOf(
            HttpAuthenticationFacadeInterface::class,
            $this->container->offsetGet(HttpAuthenticationConstants::FACADE)
        );

        static::assertEquals(
            $this->httpFacadeMock,
            $this->container->offsetGet(HttpConstants::FACADE)
        );
    }
}
