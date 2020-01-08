<?php
namespace BetaKiller\Api\Method\User;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use HTML;
use Spotman\Api\ApiMethodResponse;
use Spotman\Defence\DefinitionBuilderInterface;
use Spotman\Defence\ArgumentsInterface;

class UpdateProfileApiMethod extends AbstractEntityBasedApiMethod
{
    private const ARG_DATA = 'data';

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->composite(self::ARG_DATA);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return null|\Spotman\Api\ApiMethodResponse
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $entity = $this->getEntity($arguments);

        $data = $arguments->getArray(self::ARG_DATA);

        if (isset($data['firstName'])) {
            $user->setFirstName(HTML::chars($data['firstName']));
        }

        if (isset($data['lastName'])) {
            $user->setLastName(HTML::chars($data['lastName']));
        }

        if (isset($data['phone'])) {
            $user->setPhone(HTML::chars($data['phone']));
        }

        $this->saveEntity($entity);

        return null;
    }
}
