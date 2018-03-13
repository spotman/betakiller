<?php
namespace BetaKiller\Content\Shortcode\Attribute;


class ItemAttribute extends NumberAttribute
{
    private $relatedShortcodeName;

    public function __construct(string $name, string $relatedShortcodeName)
    {
        parent::__construct($name);

        $this->relatedShortcodeName = $relatedShortcodeName;
    }

    /**
     * Returns attribute type
     *
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_ITEM;
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
        return parent::jsonSerialize() + ['relatedShortcodeName' => $this->relatedShortcodeName];
    }
}
