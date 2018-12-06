<?php
declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\I18n\I18nConfig;
use BetaKiller\Model\Language;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class Languages extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\I18nConfig
     */
    private $config;

    /**
     * Languages constructor.
     *
     * @param \BetaKiller\I18n\I18nConfig                        $config
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(
        I18nConfig $config,
        LanguageRepositoryInterface $langRepo
    ) {
        $this->langRepo = $langRepo;
        $this->config   = $config;

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        // "lang" => "locale"
        $configLanguages = $this->config->getAllowedLanguages();

        if (!$configLanguages) {
            throw new TaskException('Define app languages in config/app.php');
        }

        $defaultLanguage = null;

        foreach ($configLanguages as $langName => $locale) {
            $model = $this->langRepo->findByIsoCode($langName);

            if (!$model) {
                $model = (new Language)
                    ->setIsoCode($langName);
            }

            // First language is default
            if (!$defaultLanguage) {
                $defaultLanguage = $model;
            }

            // Update locale
            $model->setLocale($locale);

            $this->langRepo->save($model);
        }

        if (!$defaultLanguage->isDefault()) {
            // Update default language
            $this->langRepo->setDefaultLanguage($defaultLanguage);
        }
    }
}
