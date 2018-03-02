<?php
namespace BetaKiller\Api\Method\Shortcode;

use BetaKiller\Content\Shortcode\ShortcodeFacade;
use Spotman\Api\ApiMethodResponse;

class GetAttributesDefinitionApiMethod extends AbstractShortcodeApiMethod
{
    /**
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $facade;

    /**
     * ApproveApiMethod constructor.
     *
     * @param string                                        $name
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     */
    public function __construct(string $name, ShortcodeFacade $facade)
    {
        $this->id     = ucfirst($name);
        $this->facade = $facade;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function execute(): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity */
        $entity    = $this->getEntity();
        $shortcode = $this->facade->createFromEntity($entity);

        $data = [];

        foreach ($shortcode->getAttributesDefinitions() as $definition) {
            $data[$definition->getName()] = $definition;
        }

        return $this->response($data);
    }
}
