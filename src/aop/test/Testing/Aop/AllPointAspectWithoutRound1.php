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
namespace SwoftTest\Aop\Testing\Aop;

use Swoft\Aop\Bean\Annotation\After;
use Swoft\Aop\Bean\Annotation\AfterReturning;
use Swoft\Aop\Bean\Annotation\AfterThrowing;
use Swoft\Aop\Bean\Annotation\Aspect;
use Swoft\Aop\Bean\Annotation\Before;
use Swoft\Aop\Bean\Annotation\PointBean;
use Swoft\Aop\JoinPoint;
use SwoftTest\Aop\Testing\Bean\AopBean2;

/**
 * the test of aspcet
 *
 * @Aspect
 * @PointBean(
 *     include={AopBean2::class},
 * )
 */
class AllPointAspectWithoutRound1
{
    /**
     * @var \Throwable
     */
    public static $catch;

    /**
     * @Before
     */
    public function before()
    {
        echo ' before1withoutaround ';
    }

    /**
     * @After
     */
    public function after()
    {
        echo ' after1withoutaround ';
    }

    /**
     * @AfterReturning
     */
    public function afterReturn()
    {
        echo ' afterReturn1withoutaround ';
    }

    /**
     * @param JoinPoint $joinPoint
     * @throws
     * @AfterThrowing
     */
    public function afterThrowing(JoinPoint $joinPoint)
    {
        AllPointAspectWithoutRound1::$catch = $joinPoint->getCatch();
    }
}
