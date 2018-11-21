<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Model\Token;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\TokenRepository;

class TokenService
{
    /**
     * @var \BetaKiller\Repository\TokenRepository
     */
    private $tokenRepo;

    /**
     * @param \BetaKiller\Repository\TokenRepository $tokenRepo
     */
    public function __construct(TokenRepository $tokenRepo)
    {
        $this->tokenRepo = $tokenRepo;
    }

    /**
     * Generates a random salt for each hash.
     *
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @param \DateInterval                   $ttl
     *
     * @return \BetaKiller\Model\TokenInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function create(UserInterface $userModel, \DateInterval $ttl): TokenInterface
    {
        $value      = implode('/', [
            $userModel->getID(),
            microtime(),
        ]);
        $tokenValue = password_hash($value, PASSWORD_BCRYPT);
        $tokenValue = hash('sha256', $tokenValue);
        $tokenValue = strtolower($tokenValue);

        $tokenModel = new Token();
        $endingAt   = new \DateTimeImmutable();
        $endingAt   = $endingAt->add($ttl);

        $tokenModel
            ->setUser($userModel)
            ->setValue($tokenValue)
            ->setEndingAt($endingAt);

        $this
            ->tokenRepo
            ->save($tokenModel);

        return $tokenModel;
    }

    /**
     * @param string                          $tokenValue
     *
     * @return bool
     */
    public function verify(string $tokenValue): bool
    {
        $tokenValue = strtolower($tokenValue);
        $tokeModel  = $this->tokenRepo->findActive($tokenValue);
        if ($tokeModel) {
            if ($tokenValue === $tokeModel->getValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \BetaKiller\Model\TokenInterface $tokenModel
     *
     * @return \BetaKiller\Service\TokenService
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function delete(TokenInterface $tokenModel): self
    {
        $this->tokenRepo->delete($tokenModel);

        return $this;
    }

    /**
     * Auto delete if exists.
     *
     * @param string                          $tokenValue
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function confirm(string $tokenValue): bool
    {
        $status      = false;
        $tokenModel = $this->tokenRepo->findActive($tokenValue);
        if ($tokenModel) {
            $status = $tokenModel->isActive();
            $this->delete($tokenModel);
        }

        return $status;
    }
}
