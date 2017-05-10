<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\IFace\Exception\IFaceMissingUrlException;

class PostIndexItem extends PostItem
{
    /**
     * @return \Model_ContentPost
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     */
    protected function detectContentModel()
    {
        $orm = $this->model_factory_content_post();

        $defaultUri = $orm->getDefaultUrlValue();
        $params = $this->urlParametersHelper->getUrlParameters();

        /** @var \Model_ContentPost|null $model */
        $model = $orm->findByUrlKey('uri', $defaultUri, $params);

        if (!$model) {
            throw new IFaceMissingUrlException($defaultUri, $this->getParent());
        }

        $this->urlParametersHelper->setContentPost($model);

        return parent::detectContentModel();
    }
}
