<?php

/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Console\Input;

use Swoft\Bean\Annotation\Bean;
use Swoft\Console\Helper\CommandHelper;

/**
 * Parameter input
 * @Bean
 */
class Input implements InputInterface
{
    /**
     * Resource handle
     *
     * @var resource
     */
    protected $handle = STDIN;

    /**
     * Current directory
     *
     * @var
     */
    private $pwd;

    /**
     * Full script
     *
     * @var string
     */
    private $fullScript;

    /**
     * script
     *
     * @var string
     */
    private $script;

    /**
     * Executed command
     *
     * @var string
     */
    private $command;

    /**
     * Input parameter set
     *
     * @var array
     */
    private $args = [];

    /**
     * Short options
     *
     * @var array
     */
    private $sOpts = [];

    /**
     * Long options
     *
     * @var array
     */
    private $lOpts = [];

    /**
     * @param null|array $argv
     */
    public function __construct($argv = null)
    {
        // 命令输入信息
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        // init
        $this->pwd = $this->getPwd();
        $this->fullScript = implode(' ', $argv);
        $this->script = array_shift($argv);

        // Parse parameters and options
        list($this->args, $this->sOpts, $this->lOpts) = CommandHelper::parse($argv);
        $this->command = isset($this->args[0]) ? array_shift($this->args) : null;
    }

    /**
     * Read user input
     *
     * @param null $question 信息
     * @param bool $nl       是否换行
     * @return string
     */
    public function read($question = null, $nl = false): string
    {
        \fwrite($this->handle, $question . ($nl ? "\n" : ''));
        return trim(\fgets($this->handle));
    }

    /**
     * Get all arguments
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * 是否存在某个参数
     *
     * @param string $name Argument name
     * @return bool
     */
    public function hasArg(string $name): bool
    {
        return isset($this->args[$name]);
    }

    /**
     * 获取某个参数
     *
     * @param int|null|string $name    Argument name
     * @param null            $default Default value
     * @return mixed|null
     */
    public function getArg($name, $default = null)
    {
        return $this->get($name, $default);
    }

    /**
     * 获取必要参数
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getRequiredArg(string $name)
    {
        if ('' !== $this->get($name, '')) {
            return $this->args[$name];
        }

        throw new \InvalidArgumentException(sprintf('The argument %s is required', $name));
    }

    /**
     * Get the same parameter function value
     *
     * @param array $names   Different parameter names
     * @param null  $default Default value
     * @return mixed|null
     */
    public function getSameArg(array $names, $default = null)
    {
        return $this->sameArg($names, $default);
    }

    /**
     * Get the value of the same parameter
     *
     * @param array $names   Different parameter names
     * @param null  $default Default value
     * @return mixed|null
     */
    public function sameArg(array $names, $default = null)
    {
        foreach ($names as $name) {
            if ($this->hasArg($name)) {
                return $this->get($name);
            }
        }

        return $default;
    }

    /**
     * Get Option
     *
     * @param string $name    The name
     * @param null   $default Default value
     * @return mixed|null
     */
    public function getOpt(string $name, $default = null)
    {
        if (isset($name{1})) {
            return $this->getLongOpt($name, $default);
        }

        return $this->getShortOpt($name, $default);
    }

    /**
     * 获取必须选项
     *
     * @param string $name
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    public function getRequiredOpt(string $name)
    {
        $val = $this->getOpt($name);
        if ($val === null) {
            throw new \InvalidArgumentException(sprintf('The option %s is required)', $name));
        }

        return $val;
    }

    /**
     * 是否存在某个选项
     *
     * @param string $name The name
     * @return bool
     */
    public function hasOpt(string $name): bool
    {
        return isset($this->sOpts[$name]) || isset($this->lOpts[$name]);
    }

    /**
     * Get the value of the same option
     *
     * @param array $names   Different option names. e.g ['h', 'help']
     * @param mixed $default Default value
     * @return bool|mixed|null
     */
    public function getSameOpt(array $names, $default = null)
    {
        return $this->sameOpt($names, $default);
    }

    /**
     * Get the value of the same option
     *
     * @param array $names   Different option names. e.g ['h', 'help']
     * @param mixed $default Default value
     * @return bool|mixed|null
     */
    public function sameOpt(array $names, $default = null)
    {
        foreach ($names as $name) {
            if ($this->hasOpt($name)) {
                return $this->getOpt($name);
            }
        }

        return $default;
    }

    /**
     * 获取短选项
     *
     * @param string $name    The name
     * @param null   $default Default value
     * @return mixed|null
     */
    public function getShortOpt(string $name, $default = null)
    {
        return $this->sOpts[$name] ?? $default;
    }

    /**
     * 是否存在某个短选项
     *
     * @param string $name The name
     * @return bool
     */
    public function hasSOpt(string $name): bool
    {
        return isset($this->sOpts[$name]);
    }

    /**
     * 所有短选项
     *
     * @return array
     */
    public function getShortOpts(): array
    {
        return $this->sOpts;
    }

    /**
     * 所有短选项
     *
     * @return array
     */
    public function getSOpts(): array
    {
        return $this->sOpts;
    }

    /**
     * 获取某个长选项
     *
     * @param string $name    The name
     * @param null   $default Default value
     * @return mixed|null
     */
    public function getLongOpt(string $name, $default = null)
    {
        return $this->lOpts[$name] ?? $default;
    }

    /**
     * 是否存在某个长选项
     *
     * @param string $name The name
     * @return bool
     */
    public function hasLOpt(string $name): bool
    {
        return isset($this->lOpts[$name]);
    }

    /**
     * All long options
     *
     * @return array
     */
    public function getLongOpts(): array
    {
        return $this->lOpts;
    }

    /**
     * All long options
     *
     * @return array
     */
    public function getLOpts(): array
    {
        return $this->lOpts;
    }

    /**
     * All long and short options
     *
     * @return array
     */
    public function getOpts(): array
    {
        return \array_merge($this->sOpts, $this->lOpts);
    }

    /**
     * @return string
     */
    public function getFullScript(): string
    {
        return $this->fullScript;
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * Currently executing command
     *
     * @param string $default
     * @return string
     */
    public function getCommand($default = ''): string
    {
        return $this->command ? : $default;
    }

    /**
     * Current execution directory
     *
     * @return string
     */
    public function getPwd(): string
    {
        if (!$this->pwd) {
            $this->pwd = \getcwd();
        }

        return $this->pwd;
    }

    /**
     * Get argument value
     *
     * @param string $name    The name
     * @param null   $default Default value
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        return $this->args[$name] ?? $default;
    }
}
