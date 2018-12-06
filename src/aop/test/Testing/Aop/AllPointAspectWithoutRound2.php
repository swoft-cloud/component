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
use SwoftTest\Aop\Testing\Bean\AopBean2;

/**
 *
 * @Aspect
 * @PointBean(
 *     include={AopBean2::class},
 * )
 *
 * @uses      AllPointAspectWithoutRound2
 * @version   2018年03月27日
 * @author    maijiankang <maijiankang@foxmail.com>
 * @copyright Copyright 2010-2016 swoft software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class AllPointAspectWithoutRound2
{
    /**
     * @var \Exception
     */
    public static $catch;
    
    /**
     * @Before
     */
    public function before()
    {
        echo ' before2withoutaround ';
    }

    /**
     * @After
     */
    public function after()
    {
        echo ' after2withoutaround ';
    }

    /**
     * @AfterReturning
     */
    public function afterReturn()
    {
        echo ' afterReturn2withoutaround ';
    }

    /**
     * @param \Exception $e
     * @AfterThrowing
     * @throws
     */
    public function afterThrowing(\Exception $e = null)
    {
        AllPointAspectWithoutRound2::$catch = $e;
        throw $e;
    }
}
