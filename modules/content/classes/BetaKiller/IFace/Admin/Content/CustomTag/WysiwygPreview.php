<?php
namespace BetaKiller\IFace\Admin\Content\CustomTag;

use BetaKiller\Content\Shortcode\ShortcodeInterface;
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
     * @throws \HTTP_Exception_302
     */
    public function getData(): array
    {
        /** @var ShortcodeInterface $currentTag */
        $currentTag = $this->urlParameters->getParameter(ShortcodeInterface::URL_CONTAINER_KEY);

        $attributesKeys = $this->urlParameters->getQueryPartsKeys();

        $attributes = [];

        foreach ($attributesKeys as $key) {
            $attributes[$key] = $this->urlParameters->getQueryPart($key);
        }

        $currentTag->setAttributes($attributes);

        $imageUrl = $currentTag->getWysiwygPluginPreviewSrc();

        $this->responseHelper->redirect($imageUrl);

        return [];
    }
}
