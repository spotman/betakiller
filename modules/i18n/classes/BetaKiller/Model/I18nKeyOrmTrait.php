<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait I18nKeyOrmTrait
{
    /**
     * Returns i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string|null
     */
    public function getI18nValue(LanguageInterface $lang): ?string
    {
        foreach ($this->getRawI18nValue() as $langName => $value) {
            if ($langName === $lang->getIsoCode()) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Stores i18n value for selected language
     *
     * @param \BetaKiller\Model\LanguageInterface $lang
     * @param string                              $value
     */
    public function setI18nValue(LanguageInterface $lang, string $value): void
    {
        $data = $this->getRawI18nValue();

        $data[$lang->getIsoCode()] = $value;

        $this->setRawI18nValue($data);
    }

    /**
     * Returns first not empty i18n value
     *
     * @return string
     */
    public function getAnyI18nValue(): ?string
    {
        foreach ($this->getRawI18nValue() as $value) {
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    private function getRawI18nValue(): array
    {
        $data = (string)$this->get($this->getI18nValueColumn());

        return $data ? \json_decode($data, true) : [];
    }

    private function setRawI18nValue(array $data): void
    {
        $filtered = [];

        // Remove keys with empty values
        foreach ($data as $name => $value) {
            if (!empty($value)) {
                $filtered[$name] = $value;
            }
        }

        $this->set($this->getI18nValueColumn(), \json_encode($filtered, JSON_UNESCAPED_UNICODE));
    }

    abstract protected function getI18nValueColumn(): string;
}
