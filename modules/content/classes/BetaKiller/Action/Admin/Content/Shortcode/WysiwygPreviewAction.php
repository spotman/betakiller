<?php

namespace BetaKiller\Action\Admin\Content\Shortcode;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class WysiwygPreviewAction extends AbstractAction
{
    /**
     * WysiwygPreviewAction constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $shortcodeFacade
     */
    public function __construct(private ShortcodeFacade $shortcodeFacade)
    {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = ServerRequestHelper::getUrlContainer($request);
        $entity = $params->getEntity(ShortcodeEntityInterface::MODEL_NAME);

        $shortcode = $this->shortcodeFacade->createFromEntity($entity);

        $attributesKeys = $params->getQueryPartsKeys();

        $attributes = [];

        foreach ($attributesKeys as $key) {
            $attributes[$key] = $params->getQueryPart($key);
        }

        $shortcode->setAttributes($attributes);

        $imageUrl = $shortcode->getWysiwygPluginPreviewSrc();

        return ResponseHelper::redirect($imageUrl);
    }
}
