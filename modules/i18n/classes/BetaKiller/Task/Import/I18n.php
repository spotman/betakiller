<?php
declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\I18n\FilesystemI18nKeysLoader;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class I18n extends AbstractTask
{
    public const NOTIFICATION_NEW_KEYS = 'translator/i18n/new-keys';

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $langRepo;

    /**
     * @var \BetaKiller\I18n\FilesystemI18nKeysLoader
     */
    private $filesystemLoader;

    /**
     * @var \BetaKiller\Repository\TranslationKeyRepositoryInterface
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
     * @param \BetaKiller\Repository\LanguageRepositoryInterface       $langRepo
     * @param \BetaKiller\I18n\FilesystemI18nKeysLoader                $filesystemLoader
     * @param \BetaKiller\Repository\TranslationKeyRepositoryInterface $keyRepo
     * @param \BetaKiller\Factory\EntityFactoryInterface               $entityFactory
     * @param \BetaKiller\I18n\PluralBagFormatterInterface             $formatter
     * @param \BetaKiller\Helper\NotificationHelper                    $notificationHelper
     * @param \BetaKiller\Factory\UrlHelperFactory                     $urlHelperFactory
     * @param \Psr\Log\LoggerInterface                                 $logger
     */
    public function __construct(
        LanguageRepositoryInterface $langRepo,
        FilesystemI18nKeysLoader $filesystemLoader,
        TranslationKeyRepositoryInterface $keyRepo,
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
        return [
            'force' => false,
        ];
    }

    public function run(): void
    {
        // No DB editing for now => force update
        $force = true;
//        $force = $this->getOption('force', false) !== false;

//        if ($force && !$this->confirm('Force update requested. Proceed?')) {
//            return;
//        }

        // Get all system languages
        foreach ($this->langRepo->getAppLanguages(true) as $language) {
            // Iterate all filesystem keys
            foreach ($this->filesystemLoader->loadI18nKeys() as $key) {
                $this->importKeyValue($language, $key, $force);
            }
        }

        $total = \count($this->newKeys);

        if ($total > 0) {
//            $urlHelper = $this->urlHelperFactory->create();

            $this->notification->broadcastMessage(self::NOTIFICATION_NEW_KEYS, [
                'count' => $total,
                'keys'  => $this->getMissedKeysData(/* $urlHelper */),
//                'list_url' => $urlHelper->getListEntityUrl(
//                    TranslationKey::getUrlContainerKey(),
//                    ZoneInterface::ADMIN
//                ),
            ]);
        }
    }

    private function getMissedKeysData(/* UrlHelperInterface $urlHelper */): array
    {
        $data = [];

        foreach ($this->newKeys as $missedKey) {
            $data[] = [
                'name' => $missedKey->getI18nKeyName(),
//                'url'  => $urlHelper->getReadEntityUrl($missedKey, ZoneInterface::ADMIN),
            ];
        }

        return $data;
    }

    private function importKeyValue(LanguageInterface $lang, I18nKeyInterface $key, bool $force): void
    {
        $keyName = $key->getI18nKeyName();

        // Check key model
        $keyModel = $this->keyRepo->findByKeyName($keyName);

        $newModel = !$keyModel;

        // Create new if not exists
        if (!$keyModel) {
            $keyModel = new TranslationKey;
            $keyModel->setI18nKey($keyName);

            $this->logger->debug('I18n key ":key" added', [
                ':key' => $keyName,
            ]);
        }

        // Skip existing translations
        if (!$force && $keyModel->hasI18nValue($lang)) {
            return;
        }

        $i18nValue = $key->hasI18nValue($lang) ? $key->getI18nValue($lang) : null;

        // Skip missing translations
        if (!$i18nValue) {
            return;
        }

        $keyModel->setI18nValue($lang, $i18nValue);

        $isFormatted = $this->formatter->isFormatted($i18nValue);

        if (!$keyModel->hasID() || $isFormatted !== $keyModel->isPlural()) {
            // Plural forms in formatted strings
            if ($isFormatted) {
                // Mark key as plural
                $keyModel->markAsPlural();
                $this->logger->debug('I18n key ":key" marked as plural', [
                    ':key' => $keyName,
                ]);
            } else {
                // Mark key as regular
                $keyModel->markAsRegular();
                $this->logger->debug('I18n key ":key" marked as regular', [
                    ':key' => $keyName,
                ]);
            }
        }

        $this->logger->debug('I18n key ":key" value for locale ":locale" added', [
            ':key'    => $keyName,
            ':locale' => $lang->getLocale(),
        ]);

        $this->keyRepo->save($keyModel);

        if ($newModel) {
            $this->newKeys[] = $keyModel;
        }
    }
}
