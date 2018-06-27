<?php
namespace BetaKiller\Content\Shortcode\Editor;


class EditorListingItem implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $mimeType;

    /**
     * EditorListingItem constructor.
     *
     * @param int         $id
     * @param string      $label
     * @param bool        $isValid
     * @param string|null $imageUrl
     * @param null|string $mimeType
     */
    public function __construct(int $id, string $label, bool $isValid, ?string $imageUrl, ?string $mimeType = null)
    {
        $this->id       = $id;
        $this->isValid  = $isValid;
        $this->label    = $label;
        $this->imageUrl = $imageUrl;
        $this->mimeType = $mimeType;
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
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
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
            'id'       => $this->id,
            'imageUrl' => $this->imageUrl,
            'mimeType' => $this->mimeType,
            'label'    => $this->label,
            'isValid'  => $this->isValid,
        ];
    }
}
