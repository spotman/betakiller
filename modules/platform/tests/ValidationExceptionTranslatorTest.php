<?php
declare(strict_types=1);

use BetaKiller\Helper\ExceptionTranslator;
use BetaKiller\Model\PhpException;

class ValidationExceptionTranslatorTest extends \BetaKiller\Test\AbstractTestCase
{
    public function testConstructor(): void
    {
        new ExceptionTranslator();

        $this->assertTrue(true);
    }

    public function testOrmValidationException(): void
    {
        try {
            $this->generateOrmValidationException();
        } catch (ORM_Validation_Exception $e) {
            $exception = ExceptionTranslator::fromOrmValidationException($e);

            foreach ($this->getExpectedErrorMessages() as $field => $message) {
                $this->assertEquals(
                    $message,
                    $exception->getFor($field)->getMessage(),
                    $field.' error message is not equal'
                );
            }
        }
    }

    public function testSerialization(): void
    {
        try {
            $this->generateOrmValidationException();
        } catch (ORM_Validation_Exception $e) {
            $exception = ExceptionTranslator::fromOrmValidationException($e);

            $this->assertJsonStringEqualsJsonString(
                json_encode($this->getExpectedErrorMessages()),
                json_encode($exception)
            );
        }
    }

    private function createModel(): PhpException
    {
        return new PhpException();
    }

    private function generateOrmValidationException(): void
    {
        $model = $this->createModel();

        $longStr = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';

        $model->setHash($longStr);
        $model->setMessage($longStr);

        $model->check();

        // No exception means something went wrong
        $this->assertTrue(false);
    }

    private function getExpectedErrorMessages(): array
    {
        return [
            PhpException::COLUMN_HASH         => 'hash must not exceed 64 characters long',
            PhpException::COLUMN_STATUS       => 'status must not be empty',
            PhpException::COLUMN_CREATED_AT   => 'created_at must not be empty',
            PhpException::COLUMN_LAST_SEEN_AT => 'last_seen_at must not be empty',
        ];
    }
}
