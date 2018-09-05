<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo\Providers;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Config\GeoMaxMindDownloadCitiesConfig;
use BetaKiller\Log\Logger;
use BetaKiller\Model\City;
use BetaKiller\Model\CityI18n;
use BetaKiller\Model\CityInterface;
use BetaKiller\Model\Language;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\CityI18nRepository;
use BetaKiller\Repository\CityRepository;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Task\TaskException;

class ImportCitiesMaxMindProvider extends AbstractImportCountriesCitiesMaxMind
{
    protected const TEMPLATE_FILE_NAME = 'GeoLite2-City-Locations-:language.csv';

    /**
     * @var \BetaKiller\Repository\CityRepository
     */
    private $cityRepository;

    /**
     * @var \BetaKiller\Repository\CityI18nRepository
     */
    private $cityI18NRepository;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * ImportCountries constructor.
     *
     * @param \BetaKiller\Repository\CityRepository             $cityRepository
     * @param \BetaKiller\Repository\CityI18nRepository         $cityI18NRepository
     * @param \BetaKiller\Model\UserInterface                   $user
     * @param \BetaKiller\Config\GeoMaxMindConfig               $config
     * @param \BetaKiller\Config\GeoMaxMindDownloadCitiesConfig $citiesConfig
     * @param \BetaKiller\Repository\LanguageRepository         $languageRepository
     * @param \BetaKiller\Log\Logger                            $logger
     */
    public function __construct(
        CityRepository $cityRepository,
        CityI18nRepository $cityI18NRepository,
        UserInterface $user,
        GeoMaxMindConfig $config,
        GeoMaxMindDownloadCitiesConfig $citiesConfig,
        Logger $logger,
        LanguageRepository $languageRepository
    ) {
        $this->cityRepository     = $cityRepository;
        $this->cityI18NRepository = $cityI18NRepository;

        parent::__construct($config, $citiesConfig, $languageRepository, $logger);
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $language
     */
    protected function import(array $csvLine, Language $language): void
    {
        $countryIsoCode = trim($csvLine[6]);
        $name           = trim($csvLine[5]);
        if (!$countryIsoCode && !$name) {
            return;
        }

        $cityModel = $this->importItem($csvLine, $language);
        $this->importI18n($csvLine, $language, $cityModel);
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $languageModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    private function importItem(array $csvLine, Language $languageModel): CityInterface
    {
        $csvItemId      = $csvLine[0];
        $countryIsoCode = strtoupper(trim($csvLine[6]));
        $name           = trim($csvLine[5]);

        $errorMessage = 'CSV item ID: :csvItemId. Application language locale: :locale';
        $errorData    = [':csvItemId' => $csvItemId, ':locale' => $languageModel->getLocale()];

        if (!$countryIsoCode || !$name) {
            if (!$countryIsoCode) {
                throw new TaskException('Country ISO code is empty. '.$errorMessage, $errorData);
            }
            if (!$name) {
                throw new TaskException('Name is empty. CSV item ID: :csvItemId. '.$errorMessage, $errorData);
            }
        }

        $cityModel = $this
            ->cityRepository
            ->findByMaxMindId($csvItemId);

        if (!$cityModel) {
            $languageItemsLocale = $this->config->getLanguageItemsLocale();
            if ($languageItemsLocale !== $languageModel->getLocale()) {
                throw new TaskException('Not found city by CSV item ID. '.$errorMessage, $errorData);
            }
        }

        if (!$cityModel) {
            $this->logger->debug('Import city: '.$csvItemId);
            $cityModel = (new City())
                ->setName($name)
                ->setCreatedAt()
                ->setCreatedBy($this->user)
                ->setMaxMindId($csvItemId);
            $this
                ->cityRepository
                ->save($cityModel);
        }

        return $cityModel;
    }

    /**
     * @param array                           $csvLine
     * @param \BetaKiller\Model\Language      $languageModel
     * @param \BetaKiller\Model\CityInterface $cityModel
     */
    protected function importI18n(array $csvLine, Language $languageModel, CityInterface $cityModel): void
    {
        $name = trim($csvLine[3]);

        $cityI18nModel = (new CityI18n())
            ->setCity($cityModel)
            ->setLanguage($languageModel)
            ->setValue($name);
        $this
            ->cityI18NRepository
            ->save($cityI18nModel);
    }
}

