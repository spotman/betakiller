<?php

declare(strict_types=1);

namespace BetaKiller\Api\Method\NotificationFrequency;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\NotificationFrequencyRepositoryInterface;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

readonly class ListApiMethod extends AbstractApiMethod
{
    /**
     * ListApiMethod constructor.
     *
     * @param \BetaKiller\Repository\NotificationFrequencyRepositoryInterface $repo
     * @param \BetaKiller\I18n\I18nFacade                                     $i18n
     */
    public function __construct(
        private NotificationFrequencyRepositoryInterface $repo,
        private I18nFacade $i18n
    ) {
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        // No arguments
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $lang = $user->getLanguage();
        $data = [];

        foreach ($this->repo->getAll() as $frequency) {
            $data[] = [
                'codename' => $frequency->getCodename(),
                'label'    => $this->i18n->translateHasKeyName($lang, $frequency),
            ];
        }

        return $this->response($data);
    }
}
