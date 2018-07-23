<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\IFace\Admin\AbstractAdminBase;

class WysiwygPreview extends AbstractAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @Inject
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

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
        $entity = $this->urlParameters->getEntity(ShortcodeEntityInterface::URL_CONTAINER_KEY);

        $shortcode = $this->shortcodeFacade->createFromEntity($entity);

        $attributesKeys = $this->urlParameters->getQueryPartsKeys();

        $attributes = [];

        foreach ($attributesKeys as $key) {
            $attributes[$key] = $this->urlParameters->getQueryPart($key);
        }

        $shortcode->setAttributes($attributes);

        $imageUrl = $shortcode->getWysiwygPluginPreviewSrc();

        $this->responseHelper->redirect($imageUrl);

        return [];
    }
}
