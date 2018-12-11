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

namespace Swoft\Sg\Bean\Wrapper;

use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Value;
use Swoft\Bean\Wrapper\AbstractWrapper;
use Swoft\Sg\Bean\Annotation\Breaker;

/**
 * the wrapper of breaker
 */
class BreakerWrapper extends AbstractWrapper
{
    /**
     * Class annotation
     *
     * @var array
     */
    protected $classAnnotations = [
        Breaker::class,
    ];

    /**
     * Property annotations
     *
     * @var array
     */
    protected $propertyAnnotations = [
        Inject::class,
        Value::class,
    ];

    /**
     * @param array $annotations
     * @return bool
     */
    public function isParseClassAnnotations(array $annotations): bool
    {
        return isset($annotations[Breaker::class]);
    }

    /**
     * @param array $annotations
     * @return bool
     */
    public function isParsePropertyAnnotations(array $annotations): bool
    {
        return isset($annotations[Inject::class]) || isset($annotations[Value::class]);
    }

    /**
     * @param array $annotations
     * @return bool
     */
    public function isParseMethodAnnotations(array $annotations): bool
    {
        return false;
    }
}
