<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Repository\AbstractUrlParameterRepository;

abstract class AbstractConfigBasedUrlParameter implements NonPersistentUrlParameterInterface
{
    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string
    {
        $allowed  = AbstractUrlParameterRepository::URL_KEY_NAME;
        $codename = static::getCodename();

        if ($key !== $allowed) {
            throw new UrlPrototypeException('Config-based url parameter [:name] may use ":allowed" key only', [
                ':name'    => $codename,
                ':allowed' => $allowed,
            ]);
        }

        return $codename;
    }
}
