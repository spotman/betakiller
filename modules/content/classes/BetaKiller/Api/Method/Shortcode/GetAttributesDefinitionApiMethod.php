<?php
namespace BetaKiller\Api\Method\Shortcode;

use BetaKiller\Api\Method\EntityBasedApiMethodHelper;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\DefinitionBuilderInterface;
use Spotman\Defence\ArgumentsInterface;

readonly class GetAttributesDefinitionApiMethod extends AbstractShortcodeApiMethod
{
    /**
     * ApproveApiMethod constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     */
    public function __construct(private ShortcodeFacade $facade, EntityBasedApiMethodHelper $helper)
    {
        parent::__construct($helper);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->identity('name');
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        /** @var \BetaKiller\Content\Shortcode\ShortcodeEntityInterface $entity */
        $entity    = $this->getEntity($arguments);
        $shortcode = $this->facade->createFromEntity($entity);

        $data = [];

        foreach ($shortcode->getAttributesDefinitions() as $definition) {
            $data[$definition->getName()] = $definition;
        }

        return $this->response($data);
    }
}
