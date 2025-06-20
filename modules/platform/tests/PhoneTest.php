<?php

declare(strict_types=1);

namespace BetaKiller\Test;

use BetaKiller\Model\Phone;

class PhoneTest extends AbstractTestCase
{
    /**
     * @dataProvider dbValuesDataProvider
     */
    public function testDb(string $db, string $e164, string $formatted, string $link): void
    {
        $phone = Phone::fromDb($db);

        $this->assertEquals($db, $phone->dbValue(), 'dbValue()');
        $this->assertEquals($e164, $phone->e164(), 'e164()');
        $this->assertEquals($formatted, $phone->formatted(), 'formatted()');
        $this->assertEquals($link, $phone->link(), 'link()');
    }

    public static function dbValuesDataProvider(): array
    {
        return [
            [
                '79991234567',
                '+79991234567',
                '+7 999 123-45-67',
                'tel:+7-999-123-45-67',
            ],
            [
                '79997654321',
                '+79997654321',
                '+7 999 765-43-21',
                'tel:+7-999-765-43-21',
            ],
        ];
    }
}
