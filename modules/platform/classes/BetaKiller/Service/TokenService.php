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
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @param \DateInterval                   $ttl
     *
     * @return \BetaKiller\Model\TokenInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function create(UserInterface $user, \DateInterval $ttl): TokenInterface
    {
        $value = implode('/', [
            $user->getID(),
            microtime(),
        ]);

        $tokenValue = password_hash($value, PASSWORD_BCRYPT);
        $tokenValue = mb_strtolower(hash('sha256', $tokenValue));

        $tokenModel = new Token();
        $createdAt  = new \DateTimeImmutable();
        $endingAt   = $createdAt->add($ttl);

        $tokenModel
            ->setUser($user)
            ->setValue($tokenValue)
            ->setCreatedAt($createdAt)
            ->setEndingAt($endingAt);

        $this->tokenRepo->save($tokenModel);

        return $tokenModel;
    }

    /**
     * Auto delete if exists.
     *
     * @param \BetaKiller\Model\TokenInterface $token
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function check(TokenInterface $token): bool
    {
        if (!$token->isActive()) {
            return false;
        }

        // One time tokens
        $token->setUsedAt(new \DateTimeImmutable);

        $this->tokenRepo->save($token);

        return true;
    }
}
