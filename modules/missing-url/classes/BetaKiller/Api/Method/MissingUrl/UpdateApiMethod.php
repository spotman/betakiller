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
     * @var \BetaKiller\Url\UrlDispatcher
     */
    private $urlDispatcher;

    /**
     * Override this method
     *
     * @param \BetaKiller\Model\MissingUrlModelInterface $model
     * @param                                            $data
     *
     * @throws \Spotman\Api\ApiMethodException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     * @return \BetaKiller\Model\AbstractEntityInterface|mixed|null
     */
    protected function update($model, $data)
    {
        $url = $data->targetUrl ?? null;

        if ($url) {
            if (!$this->urlDispatcher->isValidUrl($url)) {
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
