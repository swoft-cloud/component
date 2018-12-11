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
namespace Swoft\Aop\Bean\Parser;

use Swoft\Aop\Bean\Annotation\AfterReturning;
use Swoft\Aop\Bean\Collector\AspectCollector;

/**
 * the before advice of parser
 *
 * @uses      AfterReturningParser
 * @version   2017年12月24日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class AfterReturningParser extends AbstractParser
{
    /**
     * afterReturning parsing
     *
     * @param string         $className
     * @param AfterReturning $objectAnnotation
     * @param string         $propertyName
     * @param string         $methodName
     * @param null           $propertyValue
     *
     * @return null
     */
    public function parser(string $className, $objectAnnotation = null, string $propertyName = '', string $methodName = '', $propertyValue = null)
    {
        AspectCollector::collect($className, $objectAnnotation, $propertyName, $methodName, $propertyValue);

        return null;
    }
}
