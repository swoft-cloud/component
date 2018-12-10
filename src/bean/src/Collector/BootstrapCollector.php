<?php

/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Bean\Collector;

use Swoft\Bean\Annotation\Bootstrap;
use Swoft\Bean\CollectorInterface;

class BootstrapCollector implements CollectorInterface
{
    private static $bootstraps = [];

    public static function collect(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    ) {
        if ($objectAnnotation instanceof Bootstrap) {
            $name = $objectAnnotation->getName();
            $order = $objectAnnotation->getOrder();

            self::$bootstraps[$className]['name'] = $name;
            self::$bootstraps[$className]['order'] = $order;
        }
    }

    public static function getCollector(): array
    {
        return self::$bootstraps;
    }
}
