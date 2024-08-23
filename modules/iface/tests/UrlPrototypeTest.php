<?php
declare(strict_types=1);

use BetaKiller\Test\AbstractTestCase;
use BetaKiller\Url\UrlPrototype;
use BetaKiller\Url\UrlPrototypeException;

class UrlPrototypeTest extends AbstractTestCase
{
    /**
     * @dataProvider validFromStringProvider
     */
    public function testValidFromString(string $string, string $modelName, ?string $modelKey, bool $isMethodCall)
    {
        $proto = UrlPrototype::fromString($string);

        $this->assertEquals($modelName, $proto->getDataSourceName());
        $this->assertEquals($modelKey, $proto->hasModelKey() ? $proto->getModelKey() : null);
        $this->assertEquals($isMethodCall, $proto->isMethodCall());
    }

    /**
     * @dataProvider invalidFromStringProvider
     */
    public function testInvalidFromString(string $prototypeString)
    {
        $this->expectException(UrlPrototypeException::class);

        UrlPrototype::fromString($prototypeString);
    }

    /**
     * @dataProvider validFromStringProvider
     */
    public function testValidAsString(string $prototypeString, string $modelName, ?string $modelKey, bool $isMethodCall)
    {
        $proto = new UrlPrototype($modelName, $modelKey, $isMethodCall);

        $this->assertEquals($prototypeString, $proto->asString());
    }

    public function testRawParameter()
    {
        $proto = UrlPrototype::fromString('{Length}');

        $this->assertEquals($proto->isRawParameter(), true);
    }

    public function testModelKey()
    {
        $proto = UrlPrototype::fromString('{Model.key}');

        $this->assertEquals($proto->isRawParameter(), false);
        $this->assertEquals($proto->hasModelKey(), true);
        $this->assertEquals($proto->getModelKey(), 'key');
    }

    public function testModelMethod()
    {
        $proto = UrlPrototype::fromString('{Model.method()}');

        $this->assertEquals($proto->isRawParameter(), false);
        $this->assertEquals($proto->isMethodCall(), true);
        $this->assertEquals($proto->hasModelKey(), true);
        $this->assertEquals($proto->getModelKey(), 'method');
    }

    public function testModelId()
    {
        $proto = UrlPrototype::fromString('{Model.id}');

        $this->assertEquals($proto->isRawParameter(), false);
        $this->assertEquals($proto->hasIdKey(), true);
        $this->assertEquals($proto->isMethodCall(), false);
    }

    public static function validFromStringProvider(): array
    {
        return [
            ['{Model.key}', 'Model', 'key', false],
            ['{Model.method()}', 'Model', 'method', true],
            ['{RawParameter}', 'RawParameter', null, false],
        ];
    }

    public static function invalidFromStringProvider(): array
    {
        return [
            ['Model'],
            ['{Model'],
            ['{Model.}'],
            ['{Model.key[]}'],
            ['{RawParameter()}'],
        ];
    }
}
