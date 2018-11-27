<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface LanguageInterface extends DispatchableEntityInterface
{
    public const NAME_EN = 'en';
    public const NAME_DE = 'de';
    public const NAME_FR = 'fr';
    public const NAME_IT = 'it';
    public const NAME_RU = 'ru';

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setName(string $value): LanguageInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setLocale(string $value): LanguageInterface;

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setLabel(string $value): LanguageInterface;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsDefault(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonDefault(): LanguageInterface;

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markSystem(bool $value): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsSystem(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonSystem(): LanguageInterface;

    /**
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * @return bool
     */
    public function isSystem(): bool;
}
