<?php
namespace BetaKiller\Content\Shortcode\Editor;


class EditorListingItem
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $imageUrl;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * EditorListingItem constructor.
     *
     * @param int    $id
     * @param string $imageUrl
     * @param bool   $isValid
     */
    public function __construct(int $id, string $imageUrl, bool $isValid)
    {
        $this->id = $id;
        $this->imageUrl = $imageUrl;
        $this->isValid = $isValid;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }
}
