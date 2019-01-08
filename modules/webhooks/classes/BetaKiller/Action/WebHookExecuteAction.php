<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\WebHookLog;
use BetaKiller\Model\WebHookLogRequestDataAggregator;
use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\Repository\WebHookLogRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class WebHookExecuteAction extends AbstractAction
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
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function postArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\WebHook\WebHookException
     * @throws \Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var WebHookModelInterface $model */
        $model = ServerRequestHelper::getEntity($request, WebHookModelInterface::class);

        if (!$model) {
            throw new NotFoundHttpException;
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
            $webHook = $this->webHookFactory->createFromModel($model);
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
