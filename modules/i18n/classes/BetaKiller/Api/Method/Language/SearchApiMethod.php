<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\Language;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class SearchApiMethod extends AbstractApiMethod
{
    private const ARG_TERM = 'term';

    /**
     * @var \BetaKiller\Repository\LanguageRepositoryInterface
     */
    private $repo;

    /**
     * SearchApiMethod constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $repo
     */
    public function __construct(LanguageRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
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
