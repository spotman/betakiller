<?php

declare(strict_types=1);

namespace BetaKiller\Api\Method\Language;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class SearchApiMethod extends AbstractApiMethod
{
    private const ARG_TERM = 'term';

    /**
     * SearchApiMethod constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $repo
     */
    public function __construct(private LanguageRepositoryInterface $repo)
    {
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_TERM)
            ->minLength(3);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $term     = $arguments->getString(self::ARG_TERM);
        $userLang = $user->getLanguage();

        $items = [];

        foreach ($this->repo->searchByTerm($term, $userLang) as $lang) {
            $items[] = [
                'iso_code' => $lang->getIsoCode(),
                'value'    => $lang->getI18nValue($userLang),
            ];
        }

        // Sort for better UX
        usort($items, function ($item1, $item2) {
            return $item1['value'] <=> $item2['value'];
        });

        return $this->response($items);
    }
}
