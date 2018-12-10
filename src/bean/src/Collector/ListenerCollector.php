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

use Swoft\Bean\Annotation\Listener;
use Swoft\Bean\CollectorInterface;

class ListenerCollector implements CollectorInterface
{
    /**
     * @var array
     */
    private static $listeners = [];

    public static function collect(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    ) {
        if ($objectAnnotation instanceof Listener) {
            $eventName = $objectAnnotation->getEvent();
            self::$listeners[$eventName][] = $className;
        }
    }

    public static function getCollector(): array
    {
        return self::$listeners;
    }
}
