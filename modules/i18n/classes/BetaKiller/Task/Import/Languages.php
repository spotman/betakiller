<?php

declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\I18n\I18nConfigInterface;
use BetaKiller\Model\Language;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class Languages extends AbstractTask
{
    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private LanguageRepositoryInterface $langRepo;

    /**
     * @var \BetaKiller\I18n\I18nConfigInterface
     */
    protected I18nConfigInterface $config;

    /**
     * Languages constructor.
     *
     * @param \BetaKiller\I18n\I18nConfigInterface               $config
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(
        I18nConfigInterface $config,
        LanguageRepositoryInterface $langRepo
    ) {
        $this->langRepo = $langRepo;
        $this->config   = $config;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return array
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
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
