<?php
namespace BetaKiller\Api\Method\User;

use BetaKiller\Api\Method\AbstractEntityBasedApiMethod;
use BetaKiller\Model\UserInterface;
use HTML;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\ArgumentsDefinitionInterface;
use Spotman\Api\ArgumentsInterface;

class UpdateProfileApiMethod extends AbstractEntityBasedApiMethod
{
    private const ARG_DATA = 'data';

    /**
     * @return \Spotman\Api\ArgumentsDefinitionInterface
     */
    public function getArgumentsDefinition(): ArgumentsDefinitionInterface
    {
        return $this->definition()
            ->array(self::ARG_DATA);
    }

    /**
     * @param \Spotman\Api\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface $user
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
