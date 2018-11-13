<?php
declare(strict_types=1);

use BetaKiller\Model\PhpException;

class PhpExceptionModelTest extends \BetaKiller\Test\AbstractTestCase
{
    public function testStacktraceCompress(): void
    {
        $model = new PhpException;

        $trace = Debug::htmlStacktrace(new Exception());

        $model->setTrace($trace);

        $originalSize   = strlen($trace);
        $compressedSize = $model->getTraceSize();
        $ratio = (int)($originalSize / $compressedSize);

        $this->assertEquals($trace, $model->getTrace());
        $this->assertLessThan($originalSize, $compressedSize);
        $this->assertGreaterThan(5, $ratio);
    }
}
