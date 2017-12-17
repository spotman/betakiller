<?php
namespace BetaKiller\Repository;

use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlParameterInterface;

abstract class AbstractUrlParameterRepository extends AbstractReadOnlyRepository implements UrlDataSourceInterface
{
    public const URL_KEY_NAME = 'codename';

    /**
     * Creates empty entity
     *
     * @return mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function create()
    {
        throw new RepositoryException('Url parameter :repo repository can not create new entity, use findByCodename() instead', [
            ':repo' => static::getCodename(),
        ]);
    }

    /**
     * @param string $codename
     *
     * @return UrlParameterInterface|mixed
     */
    abstract public function findByCodename(string $codename);

    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return self::URL_KEY_NAME;
    }
}
