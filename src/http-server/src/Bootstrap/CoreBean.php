<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Http\Server\Bootstrap;

use Swoft\Bean\Annotation\BootBean;
use Swoft\Core\BootBeanInterface;
use Swoft\Http\Server\Parser\RequestParser;
use Swoft\Http\Server\Router\HandlerMapping;
use Swoft\Http\Server\ServerDispatcher;

/**
 * The core bean of http server
 *
 * @BootBean()
 */
class CoreBean implements BootBeanInterface
{
    /**
     * @return array
     */
    public function beans(): array
    {
        return [
            'serverDispatcher' => [
                'class' => ServerDispatcher::class,
            ],
            'httpRouter'       => [
                'class' => HandlerMapping::class,
            ],
            'requestParser'    => [
                'class' => RequestParser::class,
            ],
        ];
    }
}
