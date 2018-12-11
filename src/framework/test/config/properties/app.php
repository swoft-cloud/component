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
    'beanScan' => [
        'SwoftTest\\Testing' => BASE_PATH . '/Testing',
    ],
    'bootScan' => [],
    'env' => 'Base',
    'components' => [
        'custom' => [
            'SwoftTest',
            'SwoftTest\\Testing\\Bean' => BASE_PATH . '/Testing/Bean',
            'SwoftTest\\Testing\\Bean2' => '@root/Testing/Bean2',
        ]
    ],
    'provider' => require __DIR__ . DS . 'provider.php',
    'test' => require __DIR__ . DS . 'test.php',
];
