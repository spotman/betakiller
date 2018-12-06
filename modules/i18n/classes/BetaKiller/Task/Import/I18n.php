<?php
declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\I18n\FilesystemI18nKeysLoader;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\TranslationKeyRepository;
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
     * @var \BetaKiller\I18n\FilesystemI18nKeysLoader
     */
    private $filesystemLoader;

    /**
     * @var \BetaKiller\Repository\TranslationKeyRepository
     */
    private $keyRepo;

    /**
     * @var \BetaKiller\I18n\PluralBagFormatterInterface
     */
    private $formatter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Model\TranslationKeyModelInterface[]
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
     * @param \BetaKiller\I18n\FilesystemI18nKeysLoader       $filesystemLoader
     * @param \BetaKiller\Repository\TranslationKeyRepository $keyRepo
     * @param \BetaKiller\I18n\PluralBagFormatterInterface    $formatter
     * @param \BetaKiller\Helper\NotificationHelper           $notificationHelper
     * @param \BetaKiller\Factory\UrlHelperFactory            $urlHelperFactory
     * @param \Psr\Log\LoggerInterface                        $logger
     */
    public function __construct(
        LanguageRepository $langRepo,
        FilesystemI18nKeysLoader $filesystemLoader,
        TranslationKeyRepository $keyRepo,
        PluralBagFormatterInterface $formatter,
        NotificationHelper $notificationHelper,
        UrlHelperFactory $urlHelperFactory,
        LoggerInterface $logger
    ) {
        $this->langRepo         = $langRepo;
        $this->filesystemLoader = $filesystemLoader;
        $this->keyRepo          = $keyRepo;
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
        foreach ($this->langRepo->getAppLanguages(true) as $language) {
            // Iterate all filesystem keys
            foreach ($this->filesystemLoader->loadI18nKeys() as $key) {
                $this->importKeyValue($language, $key);
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
                'name' => $missedKey->getI18nKeyName(),
                'url'  => $urlHelper->getReadEntityUrl($missedKey, ZoneInterface::ADMIN),
            ];
        }

        return $data;
    }

    private function importKeyValue(LanguageInterface $lang, I18nKeyInterface $key): void
    {
        $keyName = $key->getI18nKeyName();

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

        $i18nValue = $key->getI18nValue($lang);

        // Skip missing translations
        if (!$i18nValue) {
            return;
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

        // Skip existing translations
        if (!$keyModel->getI18nValue($lang)) {
            $keyModel->setI18nValue($lang, $i18nValue);

            $this->logger->info('I18n key ":key" value for locale ":locale" added', [
                ':key'    => $keyName,
                ':locale' => $lang->getLocale(),
            ]);
        }

        $this->keyRepo->save($keyModel);
    }
}
