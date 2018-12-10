<?php

/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Bean\Parser;

use Swoft\Bean\Annotation\BootBean;
use Swoft\Bean\Annotation\Scope;
use Swoft\Bean\Collector\BootBeanCollector;

/**
 * Class BootBeanParser
 *
 * @package Swoft\Bean\Parser
 */
class BootBeanParser extends AbstractParser
{
    /**
     * @param string $className
     * @param BootBean $objectAnnotation
     * @param string $propertyName
     * @param string $methodName
     * @param mixed $propertyValue
     * @return array
     */
    public function parser(
        string $className,
        $objectAnnotation = null,
        string $propertyName = '',
        string $methodName = '',
        $propertyValue = null
    ): array {
        BootBeanCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);

        return [$className, Scope::SINGLETON, ''];
    }
}
