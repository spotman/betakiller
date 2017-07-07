<?php
namespace BetaKiller\IFace\Admin\Content\CustomTag;

use BetaKiller\Content\CustomTag\CustomTagInterface;
use BetaKiller\IFace\Admin\AbstractAdminBase;

class WysiwygPreview extends AbstractAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ResponseHelper
     */
    private $responseHelper;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var CustomTagInterface $currentTag */
        $currentTag = $this->urlParameters->getParameter(CustomTagInterface::URL_CONTAINER_KEY);

        $attributesKeys = $this->urlParameters->getQueryPartsKeys();

        $attributes = [];

        foreach ($attributesKeys as $key) {
            $attributes[$key] = $this->urlParameters->getQueryPart($key);
        }

        $imageUrl = $currentTag->getWysiwygPluginPreviewSrc($attributes);

        $this->responseHelper->redirect($imageUrl);

        return [];
    }
}
