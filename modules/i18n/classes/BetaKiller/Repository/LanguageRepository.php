<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\Language;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * @method LanguageInterface findById(string $id)
 * @method LanguageInterface[] getAll()
 */
final class LanguageRepository extends AbstractI18nKeyRepository implements LanguageRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return Language::TABLE_FIELD_ISO_CODE;
    }

    public function getByIsoCode(string $name): LanguageInterface
    {
        $model = $this->findByIsoCode($name);

        if (!$model) {
            throw new RepositoryException('Missing language with name ":value"', [
                ':value' => $name,
            ]);
        }

        return $model;
    }

    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\LanguageInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByIsoCode(string $name): ?LanguageInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterIsoCode($orm, $name)
            ->findOne($orm);
    }

    public function getByLocale(string $locale): LanguageInterface
    {
        $orm = $this->getOrmInstance();

        $model = $this
            ->filterLocale($orm, $locale)
            ->findOne($orm);

        if (!$model) {
            throw new RepositoryException('Missing language with locale ":value"', [
                ':value' => $locale,
            ]);
        }

        return $model;
    }

    /**
     * @param bool|null $includeDev
     *
     * @return \BetaKiller\Model\Language[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAppLanguages(bool $includeDev = null): array
    {
        $orm = $this->getOrmInstance();

        if (!$includeDev) {
            $this->filterDev($orm, false);
        }

        return $this
            ->filterApp($orm)
            ->findAll($orm);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getDefaultLanguage(): LanguageInterface
    {
        foreach ($this->getAppLanguages() as $lang) {
            if ($lang->isDefault()) {
                return $lang;
            }
        }

        throw new RepositoryException('Default language is missing');
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     */
    public function setDefaultLanguage(LanguageInterface $lang): void
    {
        $lang->markAsDefault();

        // Remove flag from current language (if exists)
        foreach ($this->getAppLanguages() as $model) {
            if (!$model->isDefault()) {
                continue;
            }

            $model->markAsNonDefault();
            $this->save($model);
        }

        $this->save($lang);
    }

    public function searchByTerm(string $term, LanguageInterface $lang): array
    {
        $orm = $this->getOrmInstance();

        $this->filterI18nValue($orm, $term, $lang, self::SEARCH_STARTING);

        return $this
            ->limit($orm, 10)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     *
     * @param int|null                               $currentPage
     * @param int|null                               $itemsPerPage
     *
     * @return array
     */
    protected function findAll(ExtendedOrmInterface $orm, int $currentPage = null, int $itemsPerPage = null): array
    {
        // Default language always placed first
        $this->placeDefaultFirst($orm);

        return parent::findAll($orm, $currentPage, $itemsPerPage);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\LanguageRepository
     */
    private function filterApp(OrmInterface $orm): self
    {
        $orm->where(Language::TABLE_FIELD_IS_APP, '=', 1);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @param bool                                      $value
     *
     * @return \BetaKiller\Repository\LanguageRepository
     */
    private function filterDev(OrmInterface $orm, bool $value): self
    {
        $orm->where(Language::TABLE_FIELD_IS_DEV, '=', $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $name
     *
     * @return \BetaKiller\Repository\LanguageRepository
     */
    private function filterIsoCode(OrmInterface $orm, string $name): self
    {
        $orm->where(Language::TABLE_FIELD_ISO_CODE, '=', $name);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $locale
     *
     * @return \BetaKiller\Repository\LanguageRepository
     */
    private function filterLocale(OrmInterface $orm, string $locale): self
    {
        $orm->where(Language::TABLE_FIELD_LOCALE, '=', $locale);

        return $this;
    }

    private function placeDefaultFirst(OrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(Language::TABLE_FIELD_IS_DEFAULT), 'DESC');

        return $this;
    }

    protected function getI18nValuesColumnName(ExtendedOrmInterface $orm): string
    {
        return $orm->object_column(Language::TABLE_FIELD_I18N);
    }

    protected function customFilterForUrlDispatching(OrmInterface $orm, UrlContainerInterface $params): void
    {
        // Dispatch only app languages (includes dev)
        $this->filterApp($orm);
    }
}
