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
namespace Swoft\Rpc\Server;

use Swoft\App;
use Swoft\Contract\DispatcherInterface;
use Swoft\Core\RequestHandler;
use Swoft\Event\AppEvent;
use Swoft\Helper\ResponseHelper;
use Swoft\Rpc\Server\Event\RpcServerEvent;
use Swoft\Rpc\Server\Middleware\HandlerAdapterMiddleware;
use Swoft\Rpc\Server\Middleware\PackerMiddleware;
use Swoft\Rpc\Server\Middleware\RouterMiddleware;
use Swoft\Rpc\Server\Middleware\UserMiddleware;
use Swoft\Rpc\Server\Middleware\ValidatorMiddleware;
use Swoft\Rpc\Server\Router\HandlerAdapter;
use Swoft\Rpc\Server\Rpc\Request;
use Swoole\Server;

/**
 * Service dispatcher
 */
class ServiceDispatcher implements DispatcherInterface
{
    /**
     * Service middlewares
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * The default of handler adapter
     *
     * @var string
     */
    protected $handlerAdapter = HandlerAdapterMiddleware::class;

    /**
     * @param array ...$params
     * @throws \Swoft\Rpc\Exception\RpcException
     * @throws \InvalidArgumentException
     */
    public function dispatch(...$params)
    {
        /**
         * @var Server $server
         * @var int    $fd
         * @var int    $fromId
         * @var string $data
         */
        list($server, $fd, $fromId, $data) = $params;

        try {
            // request middlewares
            $serviceRequest = $this->getRequest($server, $fd, $fromId, $data);
            $middlewares = $this->requestMiddleware();
            $requestHandler = new RequestHandler($middlewares, $this->handlerAdapter);

            /* @var \Swoft\Rpc\Server\Rpc\Response $response */
            $response = $requestHandler->handle($serviceRequest);
            $data = $response->getAttribute(HandlerAdapter::ATTRIBUTE);
        } catch (\Throwable $t) {
            $message = sprintf('%s %s %s', $t->getMessage(), $t->getFile(), $t->getLine());
            $data = ResponseHelper::formatData('', $message, $t->getCode());
            $data = \service_packer()->pack($data);
        } finally {
            // Release system resources
            App::trigger(AppEvent::RESOURCE_RELEASE);

            $server->send($fd, $data);
        }

        App::trigger(RpcServerEvent::AFTER_RECEIVE);
    }

    /**
     * Request middleware
     *
     * @return array
     */
    public function requestMiddleware(): array
    {
        return \array_merge($this->preMiddleware(), $this->middlewares, $this->afterMiddleware());
    }

    /**
     * Pre middleware
     *
     * @return array
     */
    public function preMiddleware(): array
    {
        return [
            PackerMiddleware::class,
            RouterMiddleware::class,
        ];
    }

    /**
     * After middleware
     *
     * @return array
     */
    public function afterMiddleware(): array
    {
        return [
            ValidatorMiddleware::class,
            UserMiddleware::class,
        ];
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param \Swoole\Server $server
     * @param int            $fd
     * @param int            $fromId
     * @param string         $data
     * @return Request
     */
    private function getRequest(Server $server, int $fd, int $fromId, string $data): Request
    {
        $serviceRequest = new Request('get', '/');

        return $serviceRequest->withAttribute(PackerMiddleware::ATTRIBUTE_SERVER, $server)
                              ->withAttribute(PackerMiddleware::ATTRIBUTE_FD, $fd)
                              ->withAttribute(PackerMiddleware::ATTRIBUTE_FROMID, $fromId)
                              ->withAttribute(PackerMiddleware::ATTRIBUTE_DATA, $data);
    }
}
