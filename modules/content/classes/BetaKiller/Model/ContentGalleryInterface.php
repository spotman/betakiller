<?php
namespace BetaKiller\Model;

interface ContentGalleryInterface extends ContentElementInterface
{
    /**
     * @return \BetaKiller\Model\ContentImageInterface[]
     */
    public function getImages(): array;

    /**
     * @param \BetaKiller\Model\ContentImageInterface $image
     *
     * @return void
     */
    public function addImage(ContentImageInterface $image): void;

    /**
     * @param \BetaKiller\Model\ContentImageInterface $image
     *
     * @return void
     */
    public function removeImage(ContentImageInterface $image): void;
}
