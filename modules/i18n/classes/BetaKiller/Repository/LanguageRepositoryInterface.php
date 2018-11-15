<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\LanguageInterface;

/**
 * Interface LanguageRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method LanguageInterface[] getAll()
 * @method LanguageInterface findById(string $id)
 * @method save(LanguageInterface $model)
 */
interface LanguageRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\LanguageInterface|null
     */
    public function findByName(string $name): ?LanguageInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface[]
     */
    public function getAllSystem(): array;

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     */
    public function setDefaultLanguage(LanguageInterface $lang): void;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getDefaultLanguage(): LanguageInterface;
}
