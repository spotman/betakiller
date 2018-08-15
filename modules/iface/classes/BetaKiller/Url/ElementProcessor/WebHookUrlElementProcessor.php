<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Model\WebHookLog;
use BetaKiller\Repository\WebHookLogRepository;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

/**
 * WebHook URL element processor
 */
class WebHookUrlElementProcessor implements UrlElementProcessorInterface
{
    /**
     * WebHook Factory
     *
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $webHookFactory;

    /**
     * @var \BetaKiller\Repository\WebHookLogRepository
     */
    private $webHookLogRepository;

    /**
     * @param \BetaKiller\Factory\WebHookFactory          $webHookFactory
     * @param \BetaKiller\Repository\WebHookLogRepository $webHookLogRepository
     */
    public function __construct(WebHookFactory $webHookFactory, WebHookLogRepository $webHookLogRepository)
    {
        $this->webHookFactory       = $webHookFactory;
        $this->webHookLogRepository = $webHookLogRepository;
    }

    /**
     * Execute processing on URL element
     *
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     * @param null|\Response                                  $response
     * @param null|\Request                                   $request
     *
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \Kohana_Exception
     */
    public function process(
        UrlElementInterface $model,
        UrlContainerInterface $urlContainer,
        ?\Response $response = null,
        ?\Request $request = null
    ): void {
        if (!($model instanceof WebHookModelInterface)) {
            throw new UrlElementProcessorException('Invalid model :class_invalid. Model must be :class_valid', [
                ':class_invalid' => \get_class($model),
                ':class_valid'   => WebHookModelInterface::class,
            ]);
        }

        $requestMethod = (string)$request->method();
        switch ($requestMethod) {
            case 'GET':
                $requestData = $_GET;
                break;
            case 'POST':
                $requestData = $_POST;
                break;
            default:
                $requestData = $_REQUEST;
                break;
        }

        $logModel = new WebHookLog();
        $logModel
            ->setCodename($model->getCodename())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRequestData($requestData);

        try {
            $exception = null;

            $webHook = $this->webHookFactory->createFromUrlElement($model);
            $webHook->process();

            $logModel->setStatus(true);
        } catch (\Throwable $exception) {
            $logModel
                ->setStatus(false)
                ->setMessage($exception->getMessage());
        }
        $this->webHookLogRepository->save($logModel);

        if ($exception) {
            throw $exception;
        }
    }
}
