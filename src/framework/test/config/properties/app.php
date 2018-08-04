<?php
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
        'SwoftTest\\Bean' => BASE_PATH . '/Cases/Bean',
        'SwoftTest\\Pool' => BASE_PATH . '/Cases/Pool',
    ],
    'bootScan' => [],
    'env' => 'Base',
    'components' => [
        'custom' => [
            'SwoftTest' => BASE_PATH . '/Cases',
            'SwoftTest'
        ]
    ],
    'provider' => require __DIR__ . DS . 'provider.php',
    'test' => require __DIR__ . DS . 'test.php',
];
