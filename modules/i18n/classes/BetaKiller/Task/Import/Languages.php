<?php
declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Model\Language;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

class Languages extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * Languages constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface              $appConfig
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(
        AppConfigInterface $appConfig,
        LanguageRepositoryInterface $langRepo
    ) {
        $this->appConfig = $appConfig;
        $this->langRepo  = $langRepo;

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
        $configLanguages = $this->appConfig->getAllowedLanguages();

        if (!$configLanguages) {
            throw new TaskException('Define app languages in config/app.php');
        }

        $defaultLanguage = null;

        foreach ($configLanguages as $langName => $locale) {
            $model = $this->langRepo->findByName($langName);

            if (!$model) {
                $model = (new Language)
                    ->setName($langName);
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
