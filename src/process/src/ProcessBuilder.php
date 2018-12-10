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

namespace Swoft\Process;

use Swoft\App;
use Swoft\Bean\BeanFactory;
use Swoft\Core\Coroutine;
use Swoft\Core\InitApplicationContext;
use Swoft\Helper\PhpHelper;
use Swoft\Process\Bean\Collector\ProcessCollector;
use Swoft\Process\Event\ProcessEvent;
use Swoft\Process\Exception\ProcessException;
use Swoole\Process as SwooleProcess;

/**
 * ProcessBuilder
 */
class ProcessBuilder
{
    /**
     * @var array
     */
    private static $processes = [];

    /**
     * @param string $name
     *
     * @return Process
     * @throws \InvalidArgumentException
     * @throws ProcessException
     */
    public static function create(string $name): Process
    {
        if (isset(self::$processes[$name])) {
            return self::$processes[$name];
        }

        list($name, $boot, $pipe, $inout, $co) = self::getProcessMapping($name);

        $swooleProcess = new SwooleProcess(function (SwooleProcess $swooleProcess) use ($name, $co, $boot) {
            $process = new Process($swooleProcess);
            if ($co) {
                self::runProcessByCo($name, $process, $boot);

                return;
            }
            self::runProcessByDefault($name, $process, $boot);
        }, $inout, $pipe);

        $process = new Process($swooleProcess);
        self::$processes[$name] = $process;

        return $process;
    }

    /**
     * @param string $name
     *
     * @return Process
     * @throws ProcessException
     */
    public static function get(string $name): Process
    {
        if (!isset(self::$processes[$name])) {
            throw new ProcessException(\sprintf('The %s process is not create, you must to create by first !', $name));
        }

        return self::$processes[$name];
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws ProcessException
     */
    private static function getProcessMapping(string $name): array
    {
        $collector = ProcessCollector::getCollector();
        if (!isset($collector[$name])) {
            throw new ProcessException(sprintf('The %s process is not exist! ', $name));
        }

        $process = $collector[$name];

        if (!isset($process['name'], $process['boot'], $process['pipe'], $process['inout'], $process['co'])) {
            throw new ProcessException(
                \sprintf('The %s process is un-complete ! data=%s', $name, \json_encode($process, JSON_UNESCAPED_UNICODE))
            );
        }

        $data = [
            $process['name'],
            $process['boot'],
            $process['pipe'],
            $process['inout'],
            $process['co'],
        ];

        return $data;
    }

    /**
     * @param string  $name
     * @param Process $process
     * @param bool    $boot
     */
    private static function runProcessByCo(string $name, Process $process, bool $boot)
    {
        Coroutine::create(function () use ($name, $process, $boot) {
            /* @var \Swoft\Process\ProcessInterface $processObject */
            $processObject = App::getBean($name);
            self::beforeProcess($name, $boot);

            if ($processObject->check()) {
                PhpHelper::call([$processObject, 'run'], [$process]);
            }

            self::afterProcess();
        });
    }

    /**
     * @param string $name
     * @param Process $process
     * @param bool $boot
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    private static function runProcessByDefault(string $name, Process $process, bool $boot)
    {
        /* @var \Swoft\Process\ProcessInterface $processObject */
        $processObject = App::getBean($name);
        self::beforeProcess($name, $boot);

        if ($processObject->check()) {
            PhpHelper::call([$processObject, 'run'], [$process]);
        }

        self::afterProcess();
    }

    /**
     * After process
     *
     * @param string $processName
     * @param bool $boot
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    private static function beforeProcess(string $processName, $boot)
    {
        if ($boot) {
            BeanFactory::reload();
            $initApplicationContext = new InitApplicationContext();
            $initApplicationContext->init();
        }

        self::waitChildProcess($processName, $boot);

        App::trigger(ProcessEvent::BEFORE_PROCESS, null, $processName);
    }

    /**
     * After process
     * @throws \InvalidArgumentException
     */
    private static function afterProcess()
    {
        App::trigger(ProcessEvent::AFTER_PROCESS);
    }

    /**
     * Wait child process
     * @param string $name
     * @param $boot
     */
    private static function waitChildProcess(string $name, $boot)
    {
        /* @var \Swoft\Process\ProcessInterface $processObject */
        $processObject = App::getBean($name);
        $hasWait = \method_exists($processObject, 'wait');

        if ($hasWait || $boot) {
            Process::signal(SIGCHLD, function ($sig) use ($name, $processObject, $hasWait) {
                while ($ret =  Process::wait(false)) {
                    if ($hasWait) {
                        $processObject->wait($ret);
                    }

                    unset(self::$processes[$name]);
                }
            });
        }
    }
}
