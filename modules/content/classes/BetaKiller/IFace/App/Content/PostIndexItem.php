<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\Url\DispatchableEntityInterface;

class PostIndexItem extends PostItem
{
    /**
     * @return \Model_ContentPost
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     */
    protected function detectContentModel()
    {
        $orm = $this->model_factory_content_post();

        $defaultUri = DispatchableEntityInterface::DEFAULT_URI;
        $params = $this->urlParametersHelper->getCurrentUrlParameters();

        /** @var \Model_ContentPost|null $entity */
        $entity = $orm->findByUrlKey('uri', $defaultUri, $params);

        if (!$entity) {
            throw new IFaceMissingUrlException($defaultUri, $this->getParent());
        }

        $this->urlParametersHelper->setContentPost($entity);

        return parent::detectContentModel();
    }
}
