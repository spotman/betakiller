<?php
namespace BetaKiller\Action\Admin\Content\Shortcode;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WysiwygPreviewAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

    /**
     * WysiwygPreviewAction constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $shortcodeFacade
     */
    public function __construct(ShortcodeFacade $shortcodeFacade)
    {
        $this->shortcodeFacade = $shortcodeFacade;
    }

    /**
     * Handle the request and return a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = ServerRequestHelper::getUrlContainer($request);
        $entity = $params->getEntity(ShortcodeEntityInterface::URL_CONTAINER_KEY);

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
