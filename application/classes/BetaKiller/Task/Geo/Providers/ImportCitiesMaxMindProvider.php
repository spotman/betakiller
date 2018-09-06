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
use BetaKiller\Repository\CountryRepository;
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
     * @var \BetaKiller\Repository\CountryRepository
     */
    private $countryRepository;

    /**
     * @param \BetaKiller\Repository\CityRepository             $cityRepository
     * @param \BetaKiller\Repository\CityI18nRepository         $cityI18NRepository
     * @param \BetaKiller\Repository\CountryRepository          $countryRepository
     * @param \BetaKiller\Model\UserInterface                   $user
     * @param \BetaKiller\Config\GeoMaxMindConfig               $config
     * @param \BetaKiller\Config\GeoMaxMindDownloadCitiesConfig $citiesConfig
     * @param \BetaKiller\Log\Logger                            $logger
     * @param \BetaKiller\Repository\LanguageRepository         $languageRepository
     */
    public function __construct(
        CityRepository $cityRepository,
        CityI18nRepository $cityI18NRepository,
        CountryRepository $countryRepository,
        UserInterface $user,
        GeoMaxMindConfig $config,
        GeoMaxMindDownloadCitiesConfig $citiesConfig,
        Logger $logger,
        LanguageRepository $languageRepository
    ) {
        $this->cityRepository     = $cityRepository;
        $this->countryRepository  = $countryRepository;
        $this->cityI18NRepository = $cityI18NRepository;
        $this->user               = $user;

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
        if ($cityModel) {
            $this->importI18n($csvLine, $language, $cityModel);
        }
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $languageModel
     *
     * @return \BetaKiller\Model\CityInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function importItem(array $csvLine, Language $languageModel): ?CityInterface
    {
        $csvItemId      = (int)$csvLine[0];
        $countryIsoCode = strtoupper(trim($csvLine[4]));
        $regionCode     = trim($csvLine[6]);
        $name           = trim($csvLine[10]);

        if (!$countryIsoCode && !$name) {
            return null;
        }
        if (!$regionCode && !$name) {
            return null;
        }

        $errorMessage = 'CSV item ID: :id. Application language locale: :locale';
        $errorData    = [':id' => $csvItemId, ':locale' => $languageModel->getLocale()];

        if (!$countryIsoCode || !$name) {
            if (!$countryIsoCode) {
                throw new TaskException('Country ISO code is empty. '.$errorMessage, $errorData);
            }
            if (!$name) {
                throw new TaskException('Name is empty. '.$errorMessage, $errorData);
            }
        }

        $cityModel = $this
            ->cityRepository
            ->findByMaxmindId($csvItemId);

        if (!$cityModel) {
            $languageItemsLocale = $this->config->getLanguageItemsLocale();
            if ($languageItemsLocale !== $languageModel->getLocale()) {
                throw new TaskException('Not found city by CSV item ID. '.$errorMessage, $errorData);
            }
        }

        if (!$cityModel) {
            $this->logger->debug(
                'Import city. Id: :id. Name: :name', [
                ':id'   => $csvItemId,
                ':name' => $name,
            ]);

            $countryModel = $this->findCountry($countryIsoCode);

            $cityModel = (new City())
                ->setCountry($countryModel)
                ->setName($name)
                ->setCreatedAt()
                ->setCreatedBy($this->user)
                ->setMaxmindId($csvItemId);
            $this
                ->cityRepository
                ->save($cityModel);
        }

        return $cityModel;
    }

    /**
     * @param string $countryIsoCode
     *
     * @return \BetaKiller\Model\CountryInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function findCountry(string $countryIsoCode): \BetaKiller\Model\CountryInterface
    {
        $countryModel = $this->countryRepository->findByIsoCode($countryIsoCode);
        if (!$countryModel) {
            throw new TaskException('Not found country. ISO code: '.$countryIsoCode);
        }

        return $countryModel;
    }

    /**
     * @param array                           $csvLine
     * @param \BetaKiller\Model\Language      $languageModel
     * @param \BetaKiller\Model\CityInterface $cityModel
     */
    private function importI18n(array $csvLine, Language $languageModel, CityInterface $cityModel): void
    {
        $name = trim($csvLine[10]);

        $cityI18nModel = $this
            ->cityI18NRepository
            ->findItem($cityModel, $languageModel);

        if ($cityI18nModel && $cityI18nModel->getValue() === $name) {
            return;
        }

        $this->logger->debug(
            'Import city i18n: :name. City MaxMind ID: :id. Application language locale: :locale', [
            ':name'   => $name,
            ':id'     => $cityModel->getMaxmindId(),
            ':locale' => $languageModel->getLocale(),
        ]);

        if ($cityI18nModel) {
            $cityI18nModel->setValue($name);
        } else {
            $cityI18nModel = (new CityI18n())
                ->setCity($cityModel)
                ->setLanguage($languageModel)
                ->setValue($name);
        }

        $this
            ->cityI18NRepository
            ->save($cityI18nModel);
    }
}

