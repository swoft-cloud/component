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
namespace Swoft\Rpc\Server\Bean\Collector;

use Swoft\Bean\CollectorInterface;
use Swoft\Rpc\Server\Bean\Annotation\Service;

/**
 * Service collector
 */
class ServiceCollector implements CollectorInterface
{
    /**
     * @var array
     */
    private static $serviceMapping = [];

    /**
     * @param string $className
     * @param null $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param null $propertyValue
     *
     * @throws \ReflectionException
     */
    public static function collect(string $className, $objectAnnotation = null, string $propertyName = '', string $methodName = '', $propertyValue = null)
    {
        // collect service
        if ($objectAnnotation instanceof Service) {
            $rc = new \ReflectionClass($className);
            $interfaces = $rc->getInterfaceNames();
            $methods = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
            $version = $objectAnnotation->getVersion();

            foreach ($interfaces as $interfaceClass) {
                foreach ($methods as $method) {
                    $methodName = $method->getName();
                    self::$serviceMapping[$interfaceClass][$version][$methodName] = [$className, $methodName];
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getCollector(): array
    {
        return self::$serviceMapping;
    }
}
