<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use Spotman\Api\ApiResponseItemInterface;
use Spotman\Defence\Parameter\ArgumentParameterInterface;

interface LanguageInterface extends DispatchableEntityInterface, I18nKeyModelInterface, ApiResponseItemInterface,
    ArgumentParameterInterface
{
    public const ISO_EN = 'en';
    public const ISO_DE = 'de';
    public const ISO_FR = 'fr';
    public const ISO_IT = 'it';
    public const ISO_RU = 'ru';

    public const API_KEY_ISO_CODE     = 'code';
    public const API_KEY_LABEL_I18N   = 'label';
    public const API_KEY_LABEL_NATIVE = 'native';
    public const API_KEY_IS_DEFAULT   = 'is_default';
    public const API_KEY_IS_APP       = 'is_app';

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setIsoCode(string $value): LanguageInterface;

    /**
     * @return string
     */
    public function getIsoCode(): string;

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
     * @param \BetaKiller\Model\LanguageInterface|null $lang
     *
     * @return string
     */
    public function getLabel(LanguageInterface $lang = null): string;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsDefault(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonDefault(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsApp(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonApp(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsDev(): LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonDev(): LanguageInterface;

    /**
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * @return bool
     */
    public function isApp(): bool;

    /**
     * @return bool
     */
    public function isDev(): bool;
}
