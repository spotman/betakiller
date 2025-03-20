<?php

declare(strict_types=1);

namespace BetaKiller\Test;

use BetaKiller\DI\Container;
use BetaKiller\I18n\FilesystemI18nKeysLoader;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\HasI18nKeyNameInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Punic\Plural;

class I18nFacadeTest extends AbstractTestCase
{
    private I18nFacade $instance;

    private LanguageInterface $language;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct(...func_get_args());

        $this->language = $this->getLang();
        $this->instance = $this->createInstance();
    }

    public function testCreateInstance(): void
    {
        // Instance will be created in constructor
        self::assertNotNull($this->instance);
    }

    public function testTranslateKeyName(): void
    {
        $value = $this->instance->translate($this->language, 'test.translate.key-name');

        $this->assertEquals('key name', $value);
    }

    public function testTranslateHasKeyName(): void
    {
        $key = new class implements HasI18nKeyNameInterface {
            public function getI18nKeyName(): string
            {
                return 'test.translate.key-name';
            }
        };

        $value = $this->instance->translate($this->language, $key);

        $this->assertEquals('key name', $value);
    }

    /**
     * @dataProvider pluralDataProvider
     */
    public function testPlural(int $count, string $expectString): void
    {
        $value = $this->instance->translate($this->language, 'test.translate.plural', [
            ':count' => $count,
        ]);

        $this->assertEquals($expectString, $value);
    }

    public static function pluralDataProvider(): array
    {
        return [
            [0, '0 bananas'],
            [1, '1 banana'],
            [20, '20 bananas'],
        ];
    }

    private function createInstance(): I18nFacade
    {
        $langRepo = $this->createMock(LanguageRepositoryInterface::class);

        $langRepo->method('getAppLanguages')->willReturn([
            $this->language,
        ]);

        $loader = Container::getInstance()->make(FilesystemI18nKeysLoader::class, [
            'langRepo' => $langRepo,
        ]);

        return Container::getInstance()->make(I18nFacade::class, [
            'langRepo' => $langRepo,
            'loader'   => $loader,
        ]);
    }

    private function getLang(): LanguageInterface
    {
        $lang = $this->createMock(LanguageInterface::class);

        $lang->method('getIsoCode')->willReturn(LanguageInterface::ISO_EN);
        $lang->method('getLocale')->willReturn('en_GB');

        return $lang;
    }
}
