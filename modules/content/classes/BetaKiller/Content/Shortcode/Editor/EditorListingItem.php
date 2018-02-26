<?php
namespace BetaKiller\Content\Shortcode\Editor;


class EditorListingItem implements \JsonSerializable
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

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'imageUrl' => $this->imageUrl,
            'isValid' => $this->isValid,
        ];
    }
}
