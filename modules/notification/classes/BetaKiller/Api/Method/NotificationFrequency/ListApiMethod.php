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

class ListApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * @var \BetaKiller\Repository\NotificationFrequencyRepositoryInterface
     */
    private $repo;

    /**
     * ListApiMethod constructor.
     *
     * @param \BetaKiller\Repository\NotificationFrequencyRepositoryInterface $repo
     * @param \BetaKiller\I18n\I18nFacade                                     $i18n
     */
    public function __construct(NotificationFrequencyRepositoryInterface $repo, I18nFacade $i18n)
    {
        $this->repo = $repo;
        $this->i18n = $i18n;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition();
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
