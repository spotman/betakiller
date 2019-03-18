<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;

class I18nKey implements I18nKeyInterface, \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isPlural;

    /**
     * @var string[]
     */
    private $data;

    /**
     * I18nKey constructor.
     *
     * @param string    $name
     * @param bool|null $isPlural
     */
    public function __construct(string $name, bool $isPlural = null)
    {
        if (!I18nFacade::isI18nKey($name)) {
            throw new I18nException('I18n key ":key" is not a valid key', [
                ':key' => $name,
            ]);
        }

        $this->name     = $name;
        $this->isPlural = $isPlural;
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return $this->isPlural;
    }

    /**
     * Returns i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     */
    public function getI18nValue(LanguageInterface $lang): string
    {
        return $this->data[$lang->getIsoCode()];
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return bool
     */
    public function hasI18nValue(LanguageInterface $lang): bool
    {
        return isset($this->data[$lang->getIsoCode()]);
    }

    /**
     * Stores i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $value
     */
    public function setI18nValue(LanguageInterface $lang, string $value): void
    {
        $this->data[$lang->getIsoCode()] = $value;
    }

    /**
     * Returns first not empty i18n value
     *
     * @return string
     */
    public function getAnyI18nValue(): string
    {
        foreach ($this->data as $value) {
            if ($value) {
                return $value;
            }
        }

        throw new \LogicException(sprintf(
            'Missing i18n any value for %s',
            $this->getI18nKeyName()
        ));
    }

    public function hasAnyI18nValue(): bool
    {
        foreach ($this->data as $value) {
            if ($value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns i18n value for selected language or value for any language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     */
    public function getI18nValueOrAny(LanguageInterface $lang): string
    {
        return $this->hasI18nValue($lang)
            ? $this->getI18nValue($lang)
            : $this->getAnyI18nValue();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
