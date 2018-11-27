<?php
namespace BetaKiller\Action\Admin\Content\Shortcode;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Content\Shortcode\ShortcodeEntityInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

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
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Spotman\Defence\ArgumentsInterface      $arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function handle(ServerRequestInterface $request, ArgumentsInterface $arguments): ResponseInterface
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
