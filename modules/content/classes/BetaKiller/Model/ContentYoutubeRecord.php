<?php
namespace BetaKiller\Model;

use Kohana_Exception;
use ORM;

class ContentYoutubeRecord extends ORM implements ContentElementInterface
{
    use OrmBasedContentElementEntityTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_table_name = 'content_youtube_records';

        $this->initializeEntityRelation();

        $this->belongs_to([
            'uploaded_by_user' => [
                'model'       => 'User',
                'foreign_key' => 'uploaded_by',
            ],
        ]);

        parent::_initialize();
    }

//    /**
//     * Rule definitions for validation
//     *
//     * @return array
//     */
//    public function rules()
//    {
//        return parent::rules() + [
//            'alt'   =>  [
//                ['not_empty']
//            ],
//        ];
//    }

    public function getYoutubeEmbedUrl(): string
    {
        return 'https://www.youtube.com/embed/'.$this->getYoutubeId();
    }

    public function getPreviewUrl(): string
    {
        return 'https://img.youtube.com/vi/'.$this->getYoutubeId().'/0.jpg';
    }

    /**
     * @param string $value
     *
     * @throws Kohana_Exception
     */
    public function setYoutubeId(string $value): void
    {
        $this->set('youtube_id', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getYoutubeId(): string
    {
        return $this->get('youtube_id');
    }

    /**
     * @param int $value
     *
     * @throws Kohana_Exception
     */
    public function setWidth(int $value): void
    {
        $this->set('width', $value);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function getWidth(): int
    {
        return (int)$this->get('width');
    }

    /**
     * @param int $value
     *
     * @throws Kohana_Exception
     */
    public function setHeight(int $value): void
    {
        $this->set('height', $value);
    }

    /**
     * @return int
     * @throws \BetaKiller\Exception
     */
    public function getHeight(): int
    {
        return (int)$this->get('height');
    }

    /**
     * Returns User model, who uploaded the file
     *
     * @return UserInterface
     */
    public function getUploadedBy(): UserInterface
    {
        return $this->get('uploaded_by_user');
    }

    /**
     * Sets user, who uploaded the file
     *
     * @param UserInterface $user
     *
     * @return \BetaKiller\Model\ContentYoutubeRecord
     */
    public function setUploadedBy(UserInterface $user): ContentYoutubeRecord
    {
        return $this->set('uploaded_by_user', $user);
    }

    /**
     * Returns true if content element has all required info
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getID()
            && $this->getEntityItemID()
            && $this->getEntitySlug()
            && $this->getYoutubeId();
    }
}
