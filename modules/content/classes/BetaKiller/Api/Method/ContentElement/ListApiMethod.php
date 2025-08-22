<?php

namespace BetaKiller\Api\Method\ContentElement;

use BetaKiller\Content\Shortcode\ContentElementShortcodeInterface;
use BetaKiller\Content\Shortcode\ShortcodeFacade;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\EntityRepository;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

final readonly class ListApiMethod extends AbstractApiMethod
{
    private const ARG_NAME           = 'name';
    private const ARG_ENTITY_SLUG    = 'slug';
    private const ARG_ENTITY_ITEM_ID = 'id';

    /**
     * ListApiMethod constructor.
     *
     * @param \BetaKiller\Content\Shortcode\ShortcodeFacade $facade
     *
     * @param \BetaKiller\Repository\EntityRepository       $entityRepository
     */
    public function __construct(
        private ShortcodeFacade $facade,
        private EntityRepository $entityRepository
    ) {
    }

    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_NAME);

        $builder
            ->string(self::ARG_ENTITY_SLUG)
            ->optional();

        $builder
            ->int(self::ARG_ENTITY_ITEM_ID)
            ->optional();
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Spotman\Api\ApiMethodException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $name = $arguments->getString(self::ARG_NAME);

        $shortcode = $this->facade->createFromCodename($name);

        if (!$shortcode instanceof ContentElementShortcodeInterface) {
            throw new ApiMethodException('Content element [:name] must implement :must', [
                ':name' => $name,
                ':must' => ContentElementShortcodeInterface::class,
            ]);
        }

        $entitySlug = $arguments->has(self::ARG_ENTITY_SLUG)
            ? $arguments->getString(self::ARG_ENTITY_SLUG)
            : null;

        $entityItemId = $arguments->has(self::ARG_ENTITY_ITEM_ID)
            ? $arguments->getInt(self::ARG_ENTITY_ITEM_ID)
            : null;

        $entity = $entitySlug
            ? $this->entityRepository->findBySlug($entitySlug)
            : null;

        // Return data
        return $this->response(
            $shortcode->getEditorListingItems($entity, $entityItemId)
        );
    }
}
