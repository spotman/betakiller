<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Model\WebHookLog;
use BetaKiller\Model\WebHookLogRequestDataAggregator;
use BetaKiller\Repository\WebHookLogRepository;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @param \Psr\Http\Message\ServerRequestInterface        $request
     *
     * @param \Response                                       $response
     *
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \BetaKiller\WebHook\WebHookException
     * @throws \Kohana_Exception
     * @throws \Throwable
     */
    public function process(
        UrlElementInterface $model,
        UrlContainerInterface $urlContainer,
        ServerRequestInterface $request,
        \Response $response
    ): void {
        if (!($model instanceof WebHookModelInterface)) {
            throw new UrlElementProcessorException('Invalid model :class_invalid. Model must be :class_valid', [
                ':class_invalid' => \get_class($model),
                ':class_valid'   => WebHookModelInterface::class,
            ]);
        }
        if (!$request) {
            throw new UrlElementProcessorException('Argument "request" must be defined');
        }

        $requestMethod = $request->getMethod();

        switch ($requestMethod) {
            case 'GET':
                $requestData = $request->getQueryParams();
                break;
            case 'POST':
                $requestData = (array)$request->getParsedBody();
                break;
            default:
                $requestData = $request->getServerParams();
                break;
        }
        $requestData = new WebHookLogRequestDataAggregator($requestData);

        $logModel = new WebHookLog();
        $logModel
            ->setCodename($model->getCodename())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRequestData($requestData);

        $exception = null;

        try {
            $webHook = $this->webHookFactory->createFromUrlElement($model);
            $webHook->process($request);
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
