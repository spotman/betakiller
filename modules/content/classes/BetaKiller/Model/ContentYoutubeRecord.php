<?php
namespace BetaKiller\Model;

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
    protected function configure(): void
    {
        $this->_table_name = 'content_youtube_records';

        $this->initializeEntityRelation();

        $this->belongs_to([
            'uploaded_by_user' => [
                'model'       => 'User',
                'foreign_key' => 'uploaded_by',
            ],
        ]);
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules()
    {
        return parent::rules() + [
            'youtube_id'   =>  [
                ['not_empty']
            ],
            'uploaded_by'   =>  [
                ['not_empty']
            ],
        ];
    }

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
     */
    public function setYoutubeId(string $value): void
    {
        $this->set('youtube_id', $value);
    }

    /**
     * @return string
     */
    public function getYoutubeId(): string
    {
        return $this->get('youtube_id');
    }

    /**
     * @param int $value
     */
    public function setWidth(int $value): void
    {
        $this->set('width', $value);
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return (int)$this->get('width');
    }

    /**
     * @param int $value
     */
    public function setHeight(int $value): void
    {
        $this->set('height', $value);
    }

    /**
     * @return int
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
            && $this->hasEntity()
            && $this->hasEntityItemID()
            && $this->getYoutubeId();
    }
}
