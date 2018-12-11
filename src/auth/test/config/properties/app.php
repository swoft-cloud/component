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

return [
    'version' => '1.0',
    'autoInitBean' => true,
    'bootScan' => [
        'Swoft\\Auth' => BASE_PATH . '/../src',
    ],
    'beanScan' => [
        'SwoftTest\\Auth\\Testing'=> BASE_PATH .'/Testing'
    ],
    'I18n' => [
        'sourceLanguage' => '@root/resources/messages/',
    ],
    'env' => 'Base',
    'auth' => [
        'jwt' => [
            'algorithm' => 'HS256',
            'secret' => '1231231'
        ]
    ],
    'Service' => [
        'user' => [
            'timeout' => 3000
        ]
    ],
    'cache' => require dirname(__FILE__) . DS . 'cache.php',
];
