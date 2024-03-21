<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserState;
use BetaKiller\Utils\Kohana\ORM;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use function mb_strtolower;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method UserInterface findById(string $id)
 * @method UserInterface getById(string $id)
 * @method UserInterface[] getAll()
 * @method void save(UserInterface $entity)
 */
class UserRepository extends AbstractHasWorkflowStateRepository implements UserRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getUrlKeyName(): string
    {
        return User::COL_ID;
    }

    /**
     * @param string $relName
     *
     * @return \Database_Expression
     */
    public static function makeFullNameExpression(string $relName): \Database_Expression
    {
        return \DB::expr(sprintf(
            'CONCAT(`%s`, " ", `%s`)',
            $relName.ORM::REL_SEP.User::COL_FIRST_NAME,
            $relName.ORM::REL_SEP.User::COL_LAST_NAME
        ));
    }

    /**
     * @param string $relName
     *
     * @return \Database_Expression[]|string[]
     */
    public static function makeSearchExpressions(string $relName): array
    {
        return [
            self::makeFullNameExpression($relName),
            $relName.ORM::COL_SEP.User::COL_FIRST_NAME,
            $relName.ORM::COL_SEP.User::COL_LAST_NAME,
            $relName.ORM::COL_SEP.User::COL_MIDDLE_NAME,
            $relName.ORM::COL_SEP.User::COL_EMAIL,
            $relName.ORM::COL_SEP.User::COL_PHONE,
//            \DB::expr(sprintf('REGEXP_REPLACE(`%s`, "[^0-9]+", "")', $relName.'.'.User::COL_PHONE)),
        ];
    }

    public function findByEmail(string $email): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        // Lowercase to prevent collisions
        return $this
            ->filterEmail($orm, mb_strtolower($email))
            ->findOne($orm);
    }

    public function findByUsername(string $username): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        // Lowercase to prevent collisions
        return $this
            ->filterUsername($orm, mb_strtolower($username))
            ->findOne($orm);
    }

    public function findByPhone(string $phone): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterPhone($orm, $phone)
            ->findOne($orm);
    }

    /**
     * Search for user by username or e-mail
     *
     * @param string $loginOrEmail
     *
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        // Lowercase to prevent collisions
        $loginOrEmail = mb_strtolower($loginOrEmail);

        $orm->where($this->getUniqueKey($loginOrEmail), '=', $loginOrEmail);

        return $this->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role): array
    {
        return $this->getUsersWithRoles([$role]);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRoles(array $roles): array
    {
        $orm = $this->getOrmInstance();

        // Multiple roles => possible duplicates
        $orm->group_by_primary_key();

        return $this
            ->filterRoles($orm, $roles)
            ->findAll($orm);
    }

    protected function getStateRelationKey(): string
    {
        return User::getWorkflowStateRelationKey();
    }

    protected function getStateCodenameColumnName(): string
    {
        return UserState::COL_CODENAME;
    }

    private function filterRole(ExtendedOrmInterface $orm, RoleInterface $role): self
    {
        $orm->join_related('roles', 'roles');
        $orm->where('roles.id', '=', $role->getID());

        return $this;
    }

    /**
     * @param OrmInterface    $orm
     * @param RoleInterface[] $roles
     *
     * @return $this
     */
    private function filterRoles(OrmInterface $orm, array $roles): self
    {
        $ids = \array_unique(\array_map(static function (RoleInterface $role) {
            return $role->getID();
        }, $roles));

        $orm->join_related('roles', 'roles');
        $orm->where('roles.id', 'IN', $ids);

        return $this;
    }

    protected function filterPhone(OrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(User::COL_PHONE), '=', $value);

        return $this;
    }

    protected function filterEmail(OrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(User::COL_EMAIL), '=', $value);

        return $this;
    }

    protected function filterUsername(OrmInterface $orm, string $value): self
    {
        $orm->where($orm->object_column(User::COL_USERNAME), '=', $value);

        return $this;
    }

    /**
     * Allows a model use both email and username as unique identifiers for login
     *
     * @param string  unique value
     *
     * @return  string  field name
     */
    private function getUniqueKey(string $value): string
    {
        return \Valid::email($value) ? 'email' : 'username';
    }
}
