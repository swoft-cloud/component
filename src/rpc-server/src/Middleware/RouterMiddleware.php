<?php
declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Rpc\Server\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Http\Message\Middleware\MiddlewareInterface;

/**
 * Service Router
 *
 * @Bean
 */
class RouterMiddleware implements MiddlewareInterface
{
    /**
     * the attributed key of service
     */
    const ATTRIBUTE = 'serviceHandler';

    /**
     * Get handler from router
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \Swoft\Rpc\Server\Exception\RpcServerException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Service data
        $data = $request->getAttribute(PackerMiddleware::ATTRIBUTE_DATA);

        $method    = $data['method'] ?? '';
        $version   = $data['version'] ?? '';
        $interface = $data['interface'] ?? '';

        /* @var \Swoft\Rpc\Server\Router\HandlerMapping $serviceRouter */
        $serviceRouter  = \bean('serviceRouter');
        $serviceHandler = $serviceRouter->getHandler($interface, $version, $method);

        // Deliver service data
        $request = $request->withAttribute(self::ATTRIBUTE, $serviceHandler);

        return $handler->handle($request);
    }
}
