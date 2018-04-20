<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Aop;

/**
 * AopTrait
 */
trait AopTrait
{

    /**
     * Execute origin method by aop
     *
     * @param string $method The execution method
     * @param array  $params The parameters of execution method
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function __proxy(string $method, array $params)
    {
        /** @var Aop $map */
        $aop   = \bean(Aop::class);
        $map   = $aop->getMap();
        $class = $this->getOriginalClassName();
        // If doesn't have any advices, then execute the origin method
        if (!isset($map[$class][$method]) || empty($map[$class][$method])) {
            return parent::$method(...$params);
        }

        // Apply advices's functionality
        $advices = $map[$class][$method];


        return $this->__doAdvice($method, $params, $advices);
    }

    /**
     * @param string $method  The execution method
     * @param array  $params  The parameters of execution method
     * @param array  $advices The advices of this object method
     *
     * @return mixed
     * @throws \Throwable
     */
    public function __doAdvice(string $method, array $params, array $advices)
    {
        $result = null;
        $advice = array_shift($advices);

        try {

            // Around
            if (isset($advice['around']) && !empty($advice['around'])) {
                $result = $this->__doPoint($advice['around'], $method, $params, $advice, $advices);
            } else {
                // Before
                if (isset($advice['before']) && !empty($advice['before'])) {
                    // The result of before point will not effect origin object method
                    $this->__doPoint($advice['before'], $method, $params, $advice, $advices);
                }
                if (0 === \count($advices)) {
                    $result = parent::$method(...$params);
                } else {
                    $this->__doAdvice($method, $params, $advices);
                }
            }

            // After
            if (isset($advice['after']) && !empty($advice['after'])) {
                $this->__doPoint($advice['after'], $method, $params, $advice, $advices, $result);
            }
        } catch (\Throwable $t) {
            if (isset($advice['afterThrowing']) && !empty($advice['afterThrowing'])) {
                return $this->__doPoint($advice['afterThrowing'], $method, $params, $advice, $advices, null, $t);
            } else {
                throw $t;
            }
        }

        // afterReturning
        if (isset($advice['afterReturning']) && !empty($advice['afterReturning'])) {
            return $this->__doPoint($advice['afterReturning'], $method, $params, $advice, $advices, $result);
        }

        return $result;
    }

    /**
     * Do pointcut
     *
     * @param array      $pointAdvice the pointcut advice
     * @param string     $method      The execution method
     * @param array      $args        The parameters of execution method
     * @param array      $advice      the advice of pointcut
     * @param array      $advices     The advices of this object method
     * @param mixed      $return
     * @param \Throwable $catch       The  Throwable object caught
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function __doPoint(
        array $pointAdvice,
        string $method,
        array $args,
        array $advice,
        array $advices,
        $return = null,
        \Throwable $catch = null
    ) {
        list($aspectClass, $aspectMethod) = $pointAdvice;

        $reflectionClass      = new \ReflectionClass($aspectClass);
        $reflectionMethod     = $reflectionClass->getMethod($aspectMethod);
        $reflectionParameters = $reflectionMethod->getParameters();

        // Bind the param of method
        $aspectArgs = [];
        foreach ($reflectionParameters as $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if ($parameterType === null) {
                $aspectArgs[] = null;
                continue;
            }

            // JoinPoint object
            $type = $parameterType->__toString();
            if ($type === JoinPoint::class) {
                $aspectArgs[] = new JoinPoint($this, $method, $args, $return, $catch);
                continue;
            }

            // ProceedingJoinPoint object
            if ($type === ProceedingJoinPoint::class) {
                $aspectArgs[] = new ProceedingJoinPoint($this, $method, $args, $advice, $advices);
                continue;
            }

            //Throwable object
            if (isset($catch) && $catch instanceof $type) {
                $aspectArgs[] = $catch;
                continue;
            }
            $aspectArgs[] = null;
        }
        $aspect = \bean($aspectClass);
        return $aspect->$aspectMethod(...$aspectArgs);
    }


}
