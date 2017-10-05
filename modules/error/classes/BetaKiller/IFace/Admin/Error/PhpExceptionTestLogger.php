<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Helper\LoggerHelperTrait;

class PhpExceptionTestLogger extends ErrorAdminBase
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $e = new \HTTP_Exception_500('This is a test');

        $this->logException($this->logger, $e);

        return [];
    }
}
