<?php
namespace BetaKiller\IFace\Admin\Error;

class PhpExceptionTestLogger extends ErrorAdminBase
{
    use BetaKiller\Helper\LoggerHelperTrait;

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
