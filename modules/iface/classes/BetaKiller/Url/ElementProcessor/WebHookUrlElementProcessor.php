<?php
namespace BetaKiller\Url\ElementProcessor;

use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Model\WebHookLog;
use BetaKiller\Model\WebHookLogRequestDataAggregator;
use BetaKiller\Repository\WebHookLogRepository;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @param \BetaKiller\Url\UrlElementInterface      $model
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\ElementProcessor\UrlElementProcessorException
     * @throws \BetaKiller\WebHook\WebHookException
     * @throws \Kohana_Exception
     * @throws \Throwable
     */
    public function process(
        UrlElementInterface $model,
        ServerRequestInterface $request
    ): ResponseInterface {
        if (!$model instanceof WebHookModelInterface) {
            throw new UrlElementProcessorException('Model must be instance of :must, but :real provided', [
                ':real' => \get_class($model),
                ':must' => WebHookModelInterface::class,
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

        // Always empty 200 OK response
        return ResponseHelper::text('OK');
    }
}
