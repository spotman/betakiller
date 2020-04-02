<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\WebHooks;

use BetaKiller\Action\WebHookExecuteAction;
use BetaKiller\Factory\WebHookFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\IFace\Admin\AbstractAdminIFace;
use BetaKiller\Model\WebHookLogInterface;
use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\Repository\WebHookLogRepository;
use Psr\Http\Message\ServerRequestInterface;

class InfoItemIFace extends AbstractAdminIFace
{
    /**
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Repository\WebHookLogRepository
     */
    private $logRepo;

    /**
     * @var \BetaKiller\IdentityConverterInterface
     */
    private $converter;

    /**
     * @param \BetaKiller\Factory\WebHookFactory          $factory
     * @param \BetaKiller\Repository\WebHookLogRepository $logRepo
     * @param \BetaKiller\IdentityConverterInterface      $converter
     */
    public function __construct(
        WebHookFactory $factory,
        WebHookLogRepository $logRepo,
        IdentityConverterInterface $converter
    ) {
        $this->factory   = $factory;
        $this->logRepo   = $logRepo;
        $this->converter = $converter;
    }

    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        /** @var WebHookModelInterface $model */
        $model = ServerRequestHelper::getEntity($request, WebHookModelInterface::class);

        $listItemsUrl = $urlHelper->makeCodenameUrl(ListItemsIFace::codename(), null, false);

        $webHook    = $this->factory->createFromModel($model);
        $definition = $webHook->getRequestDefinition();

        $param = $urlHelper->createUrlContainer()
            ->setEntity($model);

        $requestAction = $urlHelper->makeCodenameUrl(WebHookExecuteAction::codename(), $param, false);

        $codeName    = $model->getCodename();
        $serviceName = $model->getServiceName();
        $eventName   = $model->getEventName();

        return [
            'listItemsUrl' => $listItemsUrl,
            'info'         => [
                'code'    => $codeName,
                'service' => $serviceName,
                'event'   => $eventName,
            ],
            'request'      => [
                'action' => $requestAction,
                'method' => $definition->getMethod(),
                'fields' => $definition->getFields(),
            ],
            'logItems'     => $this->getLogItems($model->getCodename()),
        ];
    }

    /**
     * @param string $codeName
     *
     * @return array[
     *  [
     *      string id,
     *      string codeName,
     *      \DateTimeImmutable dateCreated,
     *      int status,
     *      string message,
     *      array requestData
     *  ],
     *  ..
     * ]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getLogItems(string $codeName): array
    {
        $logItems = $this->logRepo->getItems($codeName);

        return array_map(function (WebHookLogInterface $model) {
            return [
                'id'          => $this->converter->encode($model),
                'codeName'    => $model->getCodename(),
                'dateCreated' => $model->getCreatedAt(),
                'status'      => (int)$model->isStatusSucceeded(),
                'message'     => $model->getMessage(),
                'requestData' => $model->getRequestData()->get(),
            ];
        }, $logItems);
    }
}
