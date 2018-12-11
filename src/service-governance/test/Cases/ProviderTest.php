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
namespace SwoftTest\Sg\Cases;

class ProviderTest extends AbstractTestCase
{
    public function testRegister()
    {
        $res = provider()->select()->registerService();
        $this->assertTrue($res);
    }
}
