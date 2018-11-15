<?php
declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\I18n\FilesystemLoader;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\TranslationKeyRepository;
use BetaKiller\Repository\TranslationRepository;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ZoneInterface;
use Psr\Log\LoggerInterface;

class I18n extends AbstractTask
{
    public const NOTIFICATION_NEW_KEYS = 'translator/i18n/new-keys';

    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\FilesystemLoader
     */
    private $filesystemLoader;

    /**
     * @var \BetaKiller\Repository\TranslationKeyRepository
     */
    private $keyRepo;

    /**
     * @var \BetaKiller\Repository\TranslationRepository
     */
    private $i18nRepo;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $formatter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Model\I18nKeyModelInterface[]
     */
    private $newKeys = [];

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Factory\UrlHelperFactory
     */
    private $urlHelperFactory;

    /**
     * I18n constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepository       $langRepo
     * @param \BetaKiller\I18n\FilesystemLoader               $filesystemLoader
     * @param \BetaKiller\Repository\TranslationKeyRepository $keyRepo
     * @param \BetaKiller\Repository\TranslationRepository    $i18nRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface    $formatter
     * @param \BetaKiller\Helper\NotificationHelper           $notificationHelper
     * @param \BetaKiller\Factory\UrlHelperFactory            $urlHelperFactory
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        LanguageRepository $langRepo,
        FilesystemLoader $filesystemLoader,
        TranslationKeyRepository $keyRepo,
        TranslationRepository $i18nRepo,
        PluralBagFormatterInterface $formatter,
        NotificationHelper $notificationHelper,
        UrlHelperFactory $urlHelperFactory,
        LoggerInterface $logger
    ) {
        $this->langRepo         = $langRepo;
        $this->filesystemLoader = $filesystemLoader;
        $this->keyRepo          = $keyRepo;
        $this->i18nRepo         = $i18nRepo;
        $this->formatter        = $formatter;
        $this->logger           = $logger;
        $this->notification     = $notificationHelper;
        $this->urlHelperFactory = $urlHelperFactory;

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
        // Get all system languages
        foreach ($this->langRepo->getAll() as $language) {
            // Iterate all filesystem keys
            foreach ($this->filesystemLoader->load($language->getLocale()) as $keyName => $i18nValue) {

                // Check keyName is a valid i18n key and skip if not
                if (!I18nFacade::isI18nKey($keyName)) {
                    $this->logger->warning('I18n key ":key" is not a valid key', [
                        ':key' => $keyName,
                    ]);
                    continue;
                }

                $this->importKeyValue($language, $keyName, $i18nValue);
            }
        }

        $total = \count($this->newKeys);

        if ($total > 0) {
            $urlHelper = $this->urlHelperFactory->create();

            $this->notification->groupMessage(self::NOTIFICATION_NEW_KEYS, [
                'count'    => $total,
                'keys'     => $this->getMissedKeysData($urlHelper),
                'list_url' => $urlHelper->getListEntityUrl(
                    TranslationKey::getUrlContainerKey(),
                    ZoneInterface::ADMIN
                ),
            ]);
        }
    }

    private function getMissedKeysData(UrlHelper $urlHelper): array
    {
        $data = [];

        foreach ($this->newKeys as $missedKey) {
            $data[] = [
                'name' => $missedKey->getI18nKey(),
                'url'  => $urlHelper->getReadEntityUrl($missedKey, ZoneInterface::ADMIN),
            ];
        }

        return $data;
    }

    private function importKeyValue(LanguageInterface $lang, string $keyName, string $i18nValue): void
    {
        // Check key model
        $keyModel = $this->keyRepo->findByKeyName($keyName);

        // Create new if not exists
        if (!$keyModel) {
            $keyModel = $this->keyRepo->create();
            $keyModel->setI18nKey($keyName);
            $this->logger->info('I18n key ":key" added', [
                ':key' => $keyName,
            ]);
            $this->newKeys[] = $keyModel;
        }

        $isFormatted = $this->formatter->isFormatted($i18nValue);

        if (!$keyModel->hasID() || $isFormatted !== $keyModel->isPlural()) {
            // Plural forms in formatted strings
            if ($isFormatted) {
                // Mark key as plural
                $keyModel->markAsPlural();
                $this->logger->info('I18n key ":key" marked as plural', [
                    ':key' => $keyName,
                ]);
            } else {
                // Mark key as regular
                $keyModel->markAsRegular();
                $this->logger->info('I18n key ":key" marked as regular', [
                    ':key' => $keyName,
                ]);
            }
        }

        $this->keyRepo->save($keyModel);

        // Find value model
        $valueModel = $this->i18nRepo->findItem($keyModel, $lang);

        // Skip existing translations
        if ($valueModel) {
            return;
        }

        $valueModel = $this->i18nRepo->create()
            ->setKey($keyModel)
            ->setLanguage($lang)
            ->setValue($i18nValue);

        $this->i18nRepo->save($valueModel);

        $this->logger->info('I18n key ":key" value for locale ":locale" added', [
            ':key'    => $keyName,
            ':locale' => $lang->getLocale(),
        ]);
    }
}
