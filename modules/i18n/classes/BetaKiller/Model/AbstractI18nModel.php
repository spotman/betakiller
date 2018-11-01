<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;

abstract class AbstractI18nModel extends \ORM implements I18nModelInterface
{
    public const TABLE_FIELD_LANGUAGE_ID = 'language_id';
    public const TABLE_FIELD_VALUE       = 'value';

    private const RELATION_KEY  = 'i18nKey';
    private const RELATION_LANG = 'language';

    protected function configure(): void
    {
        $relationModelName  = $this->getI18nKeyModelName();
        $relationForeignKey = $this->getI18nKeyForeignKey();

        $this->belongs_to([
            self::RELATION_KEY  => [
                'model'       => $relationModelName,
                'foreign_key' => $relationForeignKey,
            ],
            self::RELATION_LANG => [
                'model'       => 'Language',
                'foreign_key' => self::TABLE_FIELD_LANGUAGE_ID,
            ],
        ]);
        $this->load_with([
            self::RELATION_KEY,
            self::RELATION_LANG,
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getI18nKeyModelName(): string;

    /**
     * @return string
     */
    abstract protected function getI18nKeyForeignKey(): string;

    /**
     * @return array
     */
    public function rules(): array
    {
        $keyModelForeignKey = $this->getI18nKeyForeignKey();

        return [
            $keyModelForeignKey           => [
                ['not_empty'],
                ['max_length', [':value', 11]],
            ],
            self::TABLE_FIELD_LANGUAGE_ID => [
                ['not_empty'],
                ['max_length', [':value', 11]],
            ],
            self::TABLE_FIELD_VALUE       => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $model
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setKey(I18nKeyModelInterface $model): I18nModelInterface
    {
        if (!$model instanceof AbstractEntityInterface) {
            throw new DomainException('I18n key model must implement :interface', [
                ':interface' => AbstractEntityInterface::class,
            ]);
        }

        $this->set(self::RELATION_KEY, $model);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\I18nKeyModelInterface
     */
    public function getKey(): I18nKeyModelInterface
    {
        return $this->get(self::RELATION_KEY);
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setLanguage(LanguageInterface $languageModel): I18nModelInterface
    {
        $this->set(self::TABLE_FIELD_LANGUAGE_ID, $languageModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface
    {
        return $this->get(self::RELATION_LANG);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\I18nModelInterface
     */
    public function setValue(string $value): I18nModelInterface
    {
        $this->set(self::TABLE_FIELD_VALUE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->get(self::TABLE_FIELD_VALUE);
    }
}
