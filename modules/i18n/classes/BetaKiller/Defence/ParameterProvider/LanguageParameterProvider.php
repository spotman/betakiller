<?php
declare(strict_types=1);

namespace BetaKiller\Defence\ParameterProvider;

use BetaKiller\Repository\LanguageRepositoryInterface;
use Spotman\Defence\Parameter\ArgumentParameterInterface;
use Spotman\Defence\Parameter\ParameterProviderInterface;

final class LanguageParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private LanguageRepositoryInterface $repo;

    /**
     * LanguageParameterProvider constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $repo
     */
    public function __construct(LanguageRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @inheritDoc
     */
    public function convertValue(string|int $value): ArgumentParameterInterface
    {
        return $this->repo->getByIsoCode($value);
    }
}
