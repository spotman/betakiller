<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface LanguageInterface extends AbstractEntityInterface
{
    public const NAME_EN = 'en';

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
