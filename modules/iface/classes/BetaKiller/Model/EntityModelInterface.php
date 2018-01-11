<?php
namespace BetaKiller\Model;


interface EntityModelInterface extends HasLabelInterface
{
    public const URL_CONTAINER_KEY = 'Entity';
    public const URL_KEY = 'slug';

    /**
     * Returns entity short name (may be used for url creating)
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getSlug(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\EntityModelInterface
     */
    public function setSlug(string $value): self;

    /**
     * Returns model name of the current entity
     *
     * @return string
     */
    public function getLinkedModelName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\EntityModelInterface
     */
    public function setLinkedModelName(string $value): self;

    /**
     * Returns instance of linked entity
     *
     * @param int $id
     *
     * @return \BetaKiller\Model\RelatedEntityInterface
     * @throws \BetaKiller\Exception
     */
    public function getLinkedEntityInstance($id): RelatedEntityInterface;
}
