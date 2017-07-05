<?php
namespace BetaKiller\IFace\Admin\CustomTag;

use BetaKiller\IFace\Admin\AbstractAdminBase;
use BetaKiller\Content\CustomTag\CustomTagInterface;

class WysiwygPreview extends AbstractAdminBase
{
    /**
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
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
        $currentTag = $this->urlParameters->getParameter('CustomTag');

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
