<?php
declare(strict_types=1);

namespace BetaKiller\Task\Temp;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Psr\Log\LoggerInterface;

final class ImportTranslation extends AbstractTask
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Repository\TranslationKeyRepositoryInterface
     */
    private $keyRepo;

    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * ImportTranslation constructor.
     *
     * @param \BetaKiller\I18n\I18nFacade                              $i18n
     * @param \BetaKiller\Repository\TranslationKeyRepositoryInterface $keyRepo
     * @param \Psr\Log\LoggerInterface                                 $logger
     */
    public function __construct(I18nFacade $i18n, TranslationKeyRepositoryInterface $keyRepo, LoggerInterface $logger)
    {
        parent::__construct();

        $this->keyRepo = $keyRepo;
        $this->logger  = $logger;
        $this->i18n    = $i18n;
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
            'table' => null,
        ];
    }

    public function run(): void
    {
        $table  = $this->getOption('table', true);
        $result = \DB::select()->from($table)->execute();

        $this->logger->debug('Found :count rows', [
            ':count' => $result->count(),
        ]);

        $languages = $this->i18n->getAllowedLanguages();

        foreach ($result->as_array() as $record) {
            $name = $record['key'];

            $this->logger->debug('Processing key ":name"', [
                ':name' => $name,
            ]);

            $key = $this->keyRepo->findByKeyName($name);

            if (!$key) {
                throw new TaskException('Missing i18n key ":name"', [
                    ':name' => $name,
                ]);
            }

            foreach ($languages as $lang) {
                $isoCode = $lang->getIsoCode();
                $value   = $record[\mb_strtolower($isoCode)];

                if (!$value) {
                    throw new TaskException('Missing value for i18n key ":name" in lang ":lang"', [
                        ':name' => $name,
                        ':lang' => $isoCode,
                    ]);
                }

                $key->setI18nValue($lang, $value);

                $this->keyRepo->save($key);
            }
        }
    }
}
