<?php

declare(strict_types=1);

namespace BetaKiller\Notification\Message;

use BetaKiller\I18n\I18nKey;
use BetaKiller\Notification\MessageTargetInterface;

use function count;

final class TranslatorI18nNewKeysMessage extends AbstractBroadcastMessage
{
    use NonCriticalMessageTrait;

    public static function getCodename(): string
    {
        return 'translator/i18n/new-keys';
    }

    /**
     * @param \BetaKiller\Model\I18nKeyInterface[] $newKeys
     *
     * @return static
     */
    public static function createFrom(array $newKeys): self
    {
        return self::create([
            'count' => count($newKeys),
            'keys'  => self::getMissedKeysData($newKeys /* $urlHelper */),
//                'list_url' => $urlHelper->getListEntityUrl(
//                    TranslationKey::getUrlContainerKey(),
//                    Zone::admin()
//                ),
        ]);
    }

    public static function getFactoryFor(MessageTargetInterface $target): callable
    {
        return fn() => self::createFrom([
            new I18nKey('test.missing.key'),
        ]);
    }

    /**
     * @param \BetaKiller\Model\I18nKeyInterface[] $newKeys
     *
     * @return array
     */
    private static function getMissedKeysData(array $newKeys /* UrlHelperInterface $urlHelper */): array
    {
        $data = [];

        foreach ($newKeys as $missedKey) {
            $data[] = [
                'name' => $missedKey->getI18nKeyName(),
//                'url'  => $urlHelper->getReadEntityUrl($missedKey, Zone::admin()),
            ];
        }

        return $data;
    }
}
