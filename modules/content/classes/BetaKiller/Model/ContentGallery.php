<?php
namespace BetaKiller\Model;

use ORM;

class ContentGallery extends ORM implements ContentGalleryInterface
{
    use OrmBasedContentElementEntityTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'content_galleries';

        $this->initializeEntityRelation();

        $this->has_many([
            'images' => [
                'model'       => 'ContentImage',
                'foreign_key' => 'gallery_id',
                'far_key' => 'image_id',
                'through' => 'content_gallery_images',
            ],
        ]);
    }

    private function imagesAreValid(): bool
    {
        $count = 0;

        foreach ($this->getImages() as $image) {
            $count++;
            if (!$image->isValid()) {
                return false;
            }
        }

        return $count > 0;
    }

    /**
     * Returns true if content element has all required info
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getID()
            && $this->hasEntity()
            && $this->hasEntityItemID()
            && $this->imagesAreValid();
    }

    /**
     * @param \BetaKiller\Model\ContentImageInterface $image
     *
     * @return void
     */
    public function addImage(ContentImageInterface $image): void
    {
        $this->add('images', $image);
    }

    /**
     * @param \BetaKiller\Model\ContentImageInterface $image
     *
     * @return void
     */
    public function removeImage(ContentImageInterface $image): void
    {
        $this->remove('images', $image);
    }

    /**
     * @return \BetaKiller\Model\ContentImageInterface[]
     */
    public function getImages(): array
    {
        return $this->getImagesRelation()->get_all();
    }

    private function getImagesRelation(): ContentImage
    {
        return $this->get('images');
    }
}
