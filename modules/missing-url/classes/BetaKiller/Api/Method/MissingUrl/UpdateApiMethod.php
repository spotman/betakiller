<?php
namespace BetaKiller\Api\Method\MissingUrl;

use BetaKiller\Api\Method\AbstractEntityUpdateApiApiMethod;
use Spotman\Api\ApiMethodException;

class UpdateApiMethod extends AbstractEntityUpdateApiApiMethod
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\MissingUrlRedirectTargetRepository
     */
    private $targetRepo;

    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\MissingUrlModelInterface $model
     * @param                                            $data
     *
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    protected function update($model, $data): \BetaKiller\Model\AbstractEntityInterface
    {
        $url = $data->targetUrl ?? null;

        if ($url) {
            if (!$this->urlHelper->isValidUrl($url)) {
                throw new ApiMethodException('Invalid url provided');
            }

            $targetModel = $this->targetRepo->findByUrl($url);

            if (!$targetModel) {
                $targetModel = $this->targetRepo->create()
                    ->setUrl($url);
            }

            $model->setRedirectTarget($targetModel);
        }

        $this->saveEntity();

        return true;
    }
}
