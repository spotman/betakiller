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

        $this->writeToStderr(sprintf(
            'Original stacktrace size is %s', $originalSize
        ));

        $this->writeToStderr(sprintf(
            'Compressed stacktrace size is %s', $compressedSize
        ));

        $this->writeToStderr(sprintf(
            'Compress ratio is %s', (int)($originalSize / $compressedSize)
        ));

        $this->assertEquals($trace, $model->getTrace());
        $this->assertLessThan($originalSize, $compressedSize);
    }
}
