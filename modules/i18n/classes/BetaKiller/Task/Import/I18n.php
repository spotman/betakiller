<?php

declare(strict_types=1);

namespace BetaKiller\Task\Import;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\I18n\FilesystemI18nKeysLoader;
use BetaKiller\I18n\PluralBagFormatterInterface;
use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Notification\Message\TranslatorI18nNewKeysMessage;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class I18n extends AbstractTask
{
    private const ARG_FORCE = 'force';

    /**
     * @var \BetaKiller\Model\TranslationKeyModelInterface[]
     */
    private array $newKeys = [];

    public function __construct(
        private readonly LanguageRepositoryInterface $langRepo,
        private readonly FilesystemI18nKeysLoader $filesystemLoader,
        private readonly TranslationKeyRepositoryInterface $keyRepo,
        private readonly PluralBagFormatterInterface $formatter,
        private readonly NotificationHelper $notification,
//        UrlHelperFactory $urlHelperFactory,
        private readonly LoggerInterface $logger
    ) {
//        $this->urlHelperFactory = $urlHelperFactory;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->bool(self::ARG_FORCE),
        ];
    }

    public function run(ConsoleInputInterface $params): void
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

        if ($this->newKeys) {
//            $urlHelper = $this->urlHelperFactory->create();
            $this->notification->sendBroadcast(TranslatorI18nNewKeysMessage::createFrom($this->newKeys));
        }
    }

    private function importKeyValue(LanguageInterface $lang, I18nKeyInterface $key, bool $force): void
    {
        $keyName = $key->getI18nKeyName();

        // Check key model
        $keyModel = $this->keyRepo->findByKeyName($keyName);

        $newModel = !$keyModel;

        // Create new if not exists
        if (!$keyModel) {
            $keyModel = new TranslationKey();
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
