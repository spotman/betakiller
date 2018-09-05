<?php
declare(strict_types=1);

namespace BetaKiller\Task\Geo\Providers;

use BetaKiller\Config\GeoMaxMindConfig;
use BetaKiller\Config\GeoMaxMindDownloadCountriesConfig;
use BetaKiller\Log\Logger;
use BetaKiller\Model\Country;
use BetaKiller\Model\CountryI18n;
use BetaKiller\Model\CountryInterface;
use BetaKiller\Model\Language;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\CountryI18nRepository;
use BetaKiller\Repository\CountryRepository;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Task\TaskException;

class ImportCountriesMaxMindProvider extends AbstractImportCountriesCitiesMaxMind
{
    protected const TEMPLATE_FILE_NAME = 'GeoLite2-Country-Locations-:language.csv';

    /**
     * @var \BetaKiller\Repository\CountryRepository
     */
    private $countryRepository;

    /**
     * @var \BetaKiller\Repository\CountryI18nRepository
     */
    private $countryI18NRepository;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @param \BetaKiller\Repository\CountryRepository             $countryRepository
     * @param \BetaKiller\Repository\CountryI18nRepository         $countryI18NRepository
     * @param \BetaKiller\Model\UserInterface                      $user
     * @param \BetaKiller\Config\GeoMaxMindConfig                  $config
     * @param \BetaKiller\Config\GeoMaxMindDownloadCountriesConfig $countriesConfig
     * @param \BetaKiller\Repository\LanguageRepository            $languageRepository
     * @param \BetaKiller\Log\Logger                               $logger
     */
    public function __construct(
        CountryRepository $countryRepository,
        CountryI18nRepository $countryI18NRepository,
        UserInterface $user,
        GeoMaxMindConfig $config,
        GeoMaxMindDownloadCountriesConfig $countriesConfig,
        LanguageRepository $languageRepository,
        Logger $logger
    ) {
        $this->countryRepository     = $countryRepository;
        $this->countryI18NRepository = $countryI18NRepository;
        $this->user                  = $user;

        parent::__construct($config, $countriesConfig, $languageRepository, $logger);
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $language
     */
    protected function import(array $csvLine, Language $language): void
    {
        $isoCode = trim($csvLine[2]);
        $name    = trim($csvLine[3]);
        if (!$isoCode && !$name) {
            return;
        }

        $countryModel = $this->importItem($csvLine, $language);
        $this->importI18n($csvLine, $language, $countryModel);
    }

    /**
     * @param array                      $csvLine
     * @param \BetaKiller\Model\Language $languageModel
     *
     * @return \BetaKiller\Model\CountryInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function importItem(array $csvLine, Language $languageModel): CountryInterface
    {
        $csvItemId = $csvLine[0];
        $isoCode   = strtoupper(trim($csvLine[2]));
        $name      = strtolower(trim($csvLine[3]));
        $isEu      = (bool)$csvLine[6];

        $errorMessage = 'CSV item ID: :csvItemId. Application language locale: :locale';
        $errorData    = [':csvItemId' => $csvItemId, ':locale' => $languageModel->getLocale()];

        if (!$isoCode || !$name) {
            if (!$isoCode) {
                throw new TaskException('Country ISO code is empty. '.$errorMessage, $errorData);
            }
            if (!$name) {
                throw new TaskException('Name is empty. CSV item ID: :csvItemId. '.$errorMessage, $errorData);
            }
        }

        $countryModel = $this
            ->countryRepository
            ->findByIsoCode($isoCode);

        if (!$countryModel) {
            $languageItemsLocale = $this->config->getLanguageItemsLocale();
            if ($languageItemsLocale !== $languageModel->getLocale()) {
                throw new TaskException(
                    'Not found country by ISO code. ISO code: :isoCode. '.$errorMessage,
                    $errorData + [':isoCode' => $isoCode]
                );
            }
        }

        if (!$countryModel) {
            $this->logger->debug('Import country: '.$isoCode);
            $countryModel = (new Country())
                ->setIsoCode($isoCode)
                ->setCreatedAt()
                ->setCreatedBy($this->user)
                ->setEuStatus($isEu);
            $this
                ->countryRepository
                ->save($countryModel);
        }

        return $countryModel;
    }

    /**
     * @param array                              $csvLine
     * @param \BetaKiller\Model\Language         $languageModel
     * @param \BetaKiller\Model\CountryInterface $countryModel
     */
    protected function importI18n(array $csvLine, Language $languageModel, CountryInterface $countryModel): void
    {
        $name = trim($csvLine[3]);

        $countryI18nModel = (new CountryI18n())
            ->setCountry($countryModel)
            ->setLanguage($languageModel)
            ->setValue($name);
        $this
            ->countryI18NRepository
            ->save($countryI18nModel);
    }
}

