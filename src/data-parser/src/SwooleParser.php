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

namespace Swoft\DataParser;

use Swoole\Serialize;

/**
 * Class SwooleParser
 * @package Swoft\DataParser
 * @author inhere <in.798@qq.com>
 * @link https://wiki.swoole.com/wiki/page/p-serialize.html
 */
class SwooleParser implements ParserInterface
{
    /**
     * class constructor.
     * @throws \RuntimeException
     */
    public function __construct()
    {
        if (!\class_exists(Serialize::class, false)) {
            throw new \RuntimeException("The php extension 'swoole_serialize' is required!");
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function encode($data): string
    {
        return (string)Serialize::pack($data);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function decode(string $data)
    {
        return Serialize::unpack($data);
    }
}
