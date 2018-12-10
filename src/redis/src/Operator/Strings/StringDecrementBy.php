<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Redis\Operator\Strings;

use Swoft\Redis\Operator\Command;

class StringDecrementBy extends Command
{
    /**
     * [String] decrBy
     *
     * @return string
     */
    public function getId()
    {
        return 'decrBy';
    }
}
