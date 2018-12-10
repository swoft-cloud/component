<?php

/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace SwoftTest\I18n\Cases;

use Swoft\App;
use Swoft\I18n\Translator;

class TranslatorTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function translate()
    {
        $translator = new Translator();

        // Init property
        $this->assertSame('@resources/languages/', $translator->languageDir);
        $reflectClass = new \ReflectionClass(Translator::class);
        $messagesProperty = $reflectClass->getProperty('messages');
        $messagesProperty->setAccessible(true);
        $messages = $messagesProperty->getValue($translator);
        $this->assertSame([], $messages);

        // Load
        $realLanguagesDir = App::getAlias($translator->languageDir);
        if (!file_exists($realLanguagesDir)) {
            throw new \RuntimeException(sprintf('Testing config $languageDir(%s) is invalid', $realLanguagesDir));
        }
        $loadLanguagesMethod = $reflectClass->getMethod('loadLanguages');
        $loadLanguagesMethod->setAccessible(true);
        $loadLanguagesMethod->invoke($translator, $realLanguagesDir);
        $messagesAfterLoaded = $messagesProperty->getValue($translator);
        $expected = [
            'en' => [
                'default' => [
                    'title' => 'English title'
                ],
                'msg' => [
                    'body' => 'This is a message [%s] %d'
                ],
            ],
            'zh-cn' => [
                'default' => [
                    'title' => '中文标题'
                ],
                'msg' => [
                    'body' => '这是一条消息 [%s] %d'
                ],
            ],
        ];

        $this->assertSame('English title', $messagesAfterLoaded['en']['default']['title']);
        $this->assertSame('This is a message [%s] %d', $messagesAfterLoaded['en']['msg']['body']);
        $this->assertSame('中文标题', $messagesAfterLoaded['zh-cn']['default']['title']);
        $this->assertSame('这是一条消息 [%s] %d', $messagesAfterLoaded['zh-cn']['msg']['body']);

        // Translate
        $enTitle = $translator->translate('default.title', [], 'en');
        $this->assertSame('English title', $enTitle);
        $enTitle = translate('default.title', [], 'en');
        $this->assertSame('English title', $enTitle);
        $zhcnTitle = $translator->translate('default.title', [], 'zh-cn');
        $this->assertSame('中文标题', $zhcnTitle);
        $zhcnTitle = $translator->translate('default.title', ['key' => 'value'], 'zh-cn');
        $this->assertSame('中文标题', $zhcnTitle);
        $this->assertException(function () use ($translator) {
            $translator->translate('default.title', [], 'zh-hk');
        }, \InvalidArgumentException::class);
        $this->assertException(function () use ($translator) {
            $translator->translate('default', [], 'zh-cn');
        }, \InvalidArgumentException::class);

        $enBody = $translator->translate('msg.body', ['hello world', 1], 'en');
        $this->assertSame('This is a message [hello world] 1', $enBody);
        $enBody = $translator->translate('msg.body', ['key' => 'hello world', 'int' => 1], 'en');
        $this->assertSame('This is a message [hello world] 1', $enBody);
        $enBody = $translator->translate('msg.body', [1, 'hello world'], 'en');
        $this->assertSame('This is a message [1] 0', $enBody);
        $this->assertError(function () use ($translator) {
            $translator->translate('msg.body', ['hello world'], 'en');
        }, \PHPUnit_Framework_Error_Warning::class);
    }
}
