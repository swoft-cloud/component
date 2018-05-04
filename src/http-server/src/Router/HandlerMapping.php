<?php

namespace Swoft\Http\Server\Router;

use Swoft\Http\Message\Router\HandlerMappingInterface;

/**
 * handler mapping of http
 *
 * @method get(string $route, mixed $handler, array $opts = [])
 * @method post(string $route, mixed $handler, array $opts = [])
 * @method put(string $route, mixed $handler, array $opts = [])
 * @method delete(string $route, mixed $handler, array $opts = [])
 * @method options(string $route, mixed $handler, array $opts = [])
 * @method head(string $route, mixed $handler, array $opts = [])
 * @method search(string $route, mixed $handler, array $opts = [])
 * @method trace(string $route, mixed $handler, array $opts = [])
 * @method any(string $route, mixed $handler, array $opts = [])
 */
class HandlerMapping extends AbstractRouter implements HandlerMappingInterface
{
    /** @var int */
    protected $cacheCounter = 0;

    /** @var int */
    protected $routeCounter = 0;

    /**
     * The param route cache number.
     * @var int
     */
    public $tmpCacheNumber = 300;

    /** @var string */
    public $defaultAction = 'index';

    /**
     * There are last route caches. like static routes
     * @var array[]
     * [
     *  '/user/login#GET' => [
     *      'handler' => 'handler0',
     *      'option' => [...],
     *  ],
     *  '/user/login#PUT' => [
     *      'handler' => 'handler1',
     *      'option' => [...],
     *  ],
     * ]
     */
    protected $cacheRoutes = [];

    /**
     * Flatten static routes info {@see $flatStaticRoutes}
     * @var bool
     */
    protected $flattenStatic = true;

    /**
     * flatten static routes
     * @see AbstractRouter::$staticRoutes
     * @var array
     * [
     *  '/user/login#GET' => [
     *      'handler' => 'handler0',
     *      'option' => [...],
     *  ],
     *  '/user/login#PUT' => [
     *      'handler' => 'handler1',
     *      'option' => [...],
     *  ],
     * ]
     */
    protected $flatStaticRoutes = [];

    /**
     * object constructor.
     * @param array $config
     * @throws \LogicException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (isset($config['tmpCacheNumber'])) {
            $this->tmpCacheNumber = (int)$config['tmpCacheNumber'];
        }

        if (isset($config['flattenStatic'])) {
            $this->flattenStatic = (bool)$config['flattenStatic'];
        }
    }

    /**
     * convert staticRoutes to $flatStaticRoutes
     */
    public function flattenStatics()
    {
        if ($this->flattenStatic) {
            /**
             * @var array $items eg:
             * '/user/login' => [
             *      // METHOD => [...]
             *      'GET' => [
             *          'handler' => 'handler',
             *          'option' => [...],
             *      ],
             * ]
             */
            foreach ($this->staticRoutes as $path => $items) {
                foreach ($items as $method => $conf) {
                    $this->flatStaticRoutes[$path . '#' . $method] = $conf;
                }
            }
        }
    }

    /*******************************************************************************
     * route collection
     ******************************************************************************/

    /**
     * @param string|array $methods The match request method(s).
     * e.g
     *  string: 'get'
     *  array: ['get','post']
     * @param string $route The route path string. is allow empty string. eg: '/user/login'
     * @param callable|string $handler
     * @param array $opts some option data
     * [
     *     'params' => [ 'id' => '[0-9]+', ],
     *     'defaults' => [ 'id' => 10, ],
     *     'domains'  => [ 'a-domain.com', '*.b-domain.com'],
     *     'schemas' => ['https'],
     * ]
     * @return static
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function map($methods, string $route, $handler, array $opts = []): AbstractRouter
    {
        $methods = $this->validateArguments($methods, $handler);
        list($route, $conf) = $this->prepareForMap($route, $handler, $opts);

        // it is static route
        if (self::isStaticRoute($route)) {
            foreach ($methods as $method) {
                if ($method === 'ANY') {
                    continue;
                }

                $this->routeCounter++;
                $this->staticRoutes[$route][$method] = $conf;
            }

            return $this;
        }

        $conf['original'] = $route;

        // collect param route
        $this->collectParamRoute($methods, $conf, $opts['params'] ?? []);

        return $this;
    }

    /**
     * @param string $route
     * @param mixed $handler
     * @param array $opts
     * @return array
     */
    protected function prepareForMap(string $route, $handler, array $opts): array
    {
        if (!$this->initialized) {
            $this->initialized = true;
        }

        $hasPrefix = (bool)$this->currentGroupPrefix;

        // always add '/' prefix.
        if ($route = \trim($route)) {
            $route = $route{0} === '/' ? $route : '/' . $route;
        } elseif (!$hasPrefix) {
            $route = '/';
        }

        $route = $this->currentGroupPrefix . $route;

        // setting 'ignoreLastSlash'
        if ($route !== '/' && $this->ignoreLastSlash) {
            $route = \rtrim($route, '/');
        }

        $conf = [
            'handler' => $handler,
        ];

        if ($this->currentGroupOption) {
            $opts = \array_merge($this->currentGroupOption, $opts);
        }

        if ($opts) {
            $conf['option'] = $opts;
        }

        return [$route, $conf];
    }

    /**
     * @param array $methods
     * @param array $conf
     * @param array $params
     * @throws \LogicException
     */
    protected function collectParamRoute(array $methods, array $conf, array $params)
    {
        list($first, $conf) = $this->parseParamRoute($conf, $this->getAvailableParams($params));

        // route string have regular
        if ($first) {
            $conf['methods'] = \implode(',', $methods) . ',';
            $this->routeCounter++;
            $this->regularRoutes[$first][] = $conf;

            return;
        }

        foreach ($methods as $method) {
            if ($method === 'ANY') {
                continue;
            }

            $this->routeCounter++;
            $this->vagueRoutes[$method][] = $conf;
        }
    }

    /*******************************************************************************
     * route match
     ******************************************************************************/

    /**
     * find the matched route info for the given request uri path
     * @param string $method
     * @param string $path
     * @return array
     */
    public function match(string $path, string $method = 'GET'): array
    {
        // if enable 'matchAll'
        if ($matchAll = $this->matchAll) {
            if (\is_string($matchAll) && $matchAll{0} === '/') {
                $path = $matchAll;
            } elseif (\is_callable($matchAll)) {
                return [self::FOUND, $path, [
                    'handler' => $matchAll,
                ]];
            }
        }

        $path = RouteHelper::formatUriPath($path, $this->ignoreLastSlash);
        $method = \strtoupper($method);

        // is a static route path
        if ($this->staticRoutes && ($routeInfo = $this->findInStaticRoutes($path, $method))) {
            return [self::FOUND, $path, $routeInfo];
        }

        $cacheKey = $path . '#' . $method;

        // find in route caches.
        if ($this->cacheRoutes && isset($this->cacheRoutes[$cacheKey])) {
            return [self::FOUND, $path, $this->cacheRoutes[$cacheKey]];
        }

        $first = null;
        $allowedMethods = [];

        // eg '/article/12'
        if ($pos = \strpos($path, '/', 1)) {
            $first = \substr($path, 1, $pos - 1);
        }

        // is a regular dynamic route(the first node is 1th level index key).
        if ($first && isset($this->regularRoutes[$first])) {
            $result = $this->findInRegularRoutes($first, $path, $method);

            if ($result[0] === self::FOUND) {
                return $result;
            }

            $allowedMethods = $result[1];
        }

        // is a irregular dynamic route
        if ($result = $this->findInVagueRoutes($path, $method)) {
            return $result;
        }

        // For HEAD requests, attempt fallback to GET
        if ($method === 'HEAD') {
            $cacheKey = $path . '#GET';

            if (isset($this->cacheRoutes[$cacheKey])) {
                return [self::FOUND, $path, $this->cacheRoutes[$cacheKey]];
            }

            if ($routeInfo = $this->findInStaticRoutes($path, 'GET')) {
                return [self::FOUND, $path, $routeInfo];
            }

            if ($first && isset($this->regularRoutes[$first])) {
                $result = $this->findInRegularRoutes($first, $path, 'GET');

                if ($result[0] === self::FOUND) {
                    return $result;
                }
            }

            if ($result = $this->findInVagueRoutes($path, 'GET')) {
                return $result;
            }
        }

        // If nothing else matches, try fallback routes. $router->any('*', 'handler');
        if ($this->staticRoutes && ($routeInfo = $this->findInStaticRoutes('/*', $method))) {
            return [self::FOUND, $path, $routeInfo];
        }

        if ($this->notAllowedAsNotFound) {
            return [self::NOT_FOUND, $path, null];
        }

        // collect allowed methods from: staticRoutes, vagueRoutes OR return not found.
        return $this->findAllowedMethods($path, $method, $allowedMethods);
    }

    /*******************************************************************************
     * helper methods
     ******************************************************************************/

    /**
     * @param string $path
     * @param string $method
     * @param array $allowedMethods
     * @return array
     */
    protected function findAllowedMethods(string $path, string $method, array $allowedMethods): array
    {
        if (isset($this->staticRoutes[$path])) {
            $allowedMethods = \array_merge($allowedMethods, \array_keys($this->staticRoutes[$path]));
        }

        foreach ($this->vagueRoutes as $m => $routes) {
            if ($method === $m) {
                continue;
            }

            if ($this->findInVagueRoutes($path, $m)) {
                $allowedMethods[] = $method;
            }
        }

        if ($allowedMethods && ($list = \array_unique($allowedMethods))) {
            return [self::METHOD_NOT_ALLOWED, $path, $list];
        }

        // oo ... not found
        return [self::NOT_FOUND, $path, null];
    }

    /**
     * @param string $path
     * @param string $method
     * @return array|false
     */
    protected function findInStaticRoutes(string $path, string $method)
    {
        // if flattenStatic is TRUE
        if ($this->flatStaticRoutes) {
            $key = $path . '#' . $method;

            if (isset($this->flatStaticRoutes[$key])) {
                return $this->flatStaticRoutes[$key];
            }
        } elseif (isset($this->staticRoutes[$path][$method])) {
            return $this->staticRoutes[$path][$method];
        }

        return false;
    }

    /**
     * @param string $first
     * @param string $path
     * @param string $method
     * @return array
     */
    protected function findInRegularRoutes(string $first, string $path, string $method): array
    {
        $allowedMethods = '';
        /** @var array $routesInfo */
        $routesInfo = $this->regularRoutes[$first];

        foreach ($routesInfo as $conf) {
            if (0 === \strpos($path, $conf['start']) && \preg_match($conf['regex'], $path, $matches)) {
                $allowedMethods .= $conf['methods'];

                if (false !== \strpos($conf['methods'], $method . ',')) {
                    $conf = $this->mergeMatches($matches, $conf);

                    if ($this->tmpCacheNumber > 0) {
                        $this->cacheMatchedParamRoute($path, $method, $conf);
                    }

                    return [self::FOUND, $path, $conf];
                }
            }
        }

        return [
            self::NOT_FOUND,
            $allowedMethods ? \explode(',', \rtrim($allowedMethods, ',')) : []
        ];
    }

    /**
     * @param string $path
     * @param string $method
     * @return array|false
     */
    protected function findInVagueRoutes(string $path, string $method)
    {
        if (!isset($this->vagueRoutes[$method])) {
            return false;
        }

        /** @var array $routeList */
        $routeList = $this->vagueRoutes[$method];

        foreach ($routeList as $conf) {
            if ($conf['start'] && 0 !== \strpos($path, $conf['start'])) {
                continue;
            }

            if (\preg_match($conf['regex'], $path, $matches)) {
                $conf = $this->mergeMatches($matches, $conf);

                if ($this->tmpCacheNumber > 0) {
                    $this->cacheMatchedParamRoute($path, $method, $conf);
                }

                return [self::FOUND, $path, $conf];
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $conf
     */
    protected function cacheMatchedParamRoute(string $path, string $method, array $conf)
    {
        $cacheNumber = (int)$this->tmpCacheNumber;
        $cacheKey = $path . '#' . $method;

        // cache last $cacheNumber routes.
        if ($cacheNumber > 0 && !isset($this->cacheRoutes[$cacheKey])) {
            if ($this->cacheCounter >= $cacheNumber) {
                \array_shift($this->cacheRoutes);
            }

            $this->cacheCounter++;
            $this->cacheRoutes[$cacheKey] = $conf;
        }
    }

    /**
     * @return array[]
     */
    public function getCacheRoutes(): array
    {
        return $this->cacheRoutes;
    }

    /**
     * @return int
     */
    public function getCacheCounter(): int
    {
        return $this->cacheCounter;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->routeCounter;
    }

    /*******************************************************************************
     * other helper methods(for swoft)
     ******************************************************************************/

    /**
     * get handler from router
     *
     * @param array ...$params
     *
     * @return array
     */
    public function getHandler(...$params): array
    {
        list($path, $method) = $params;
        // list($path, $info) = $router;

        return $this->match($path, $method);
    }

    /**
     * 自动注册路由
     *
     * @param array $requestMapping
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function registerRoutes(array $requestMapping)
    {
        foreach ($requestMapping as $className => $mapping) {
            if (!isset($mapping['prefix'], $mapping['routes'])) {
                continue;
            }

            // controller prefix
            $controllerPrefix = $mapping['prefix'] ?: $this->getControllerPrefix($className);

            // 注册控制器对应的一组路由
            $this->registerRoute($className, $mapping['routes'], $controllerPrefix);
        }
    }

    /**
     * Registered route
     * @param string $className Class name
     * @param array $routes Route list in the controller
     * @param string $controllerPrefix Controller prefix
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function registerRoute(string $className, array $routes, string $controllerPrefix)
    {
        $routePrefix = '/' . \trim($controllerPrefix, '/');

        // Circular Registration Route
        foreach ($routes as $route) {
            if (!isset($route['route'], $route['method'], $route['action'])) {
                continue;
            }

            $mapRoute = \trim($route['route']);
            $action   = $route['action'];

            if ($mapRoute === '@') {
                $path = $routePrefix;
            } else {
                // 为空时，使用action名称When empty, use the action name
                $other = $mapRoute ?: $action;

                // '/' 开头的路由是一个单独的路由 未使用'/' 需要和控制器prefix组拼成一个路由
                $path = $other[0] === '/' ? $other : $routePrefix . '/' . $other;
            }

            $handler = $className . '@' . $action;

            // register route
            $this->map($route['method'], $path, $handler, [
                'params' => $route['params'] ?? []
            ]);
        }
    }

    /**
     * Get controller route prefix
     *
     * @param string $className        Controller class name
     * @return string
     */
    private function getControllerPrefix(string $className): string
    {
        // 解析控制器prefix
        $regex  = '/^.*\\\(\w+)' . $this->controllerSuffix . '$/';
        $prefix = '';

        if ($result = \preg_match($regex, $className, $match)) {
            $prefix = '/' . \lcfirst($match[1]);
        }

        return $prefix;
    }
}
