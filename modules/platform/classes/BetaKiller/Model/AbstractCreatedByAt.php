<?php
declare(strict_types=1);

namespace BetaKiller\Model;

abstract class AbstractCreatedByAt extends AbstractCreatedAt implements CreatedByAtInterface
{
    public const REL_CREATED_BY = 'createdBy';

    protected function configure(): void
    {
        $this->belongs_to([
            self::REL_CREATED_BY => [
                'model'       => User::getModelName(),
                'foreign_key' => static::getCreatedByColumnName(),
            ],
        ]);

        $this->load_with([
            self::REL_CREATED_BY,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            static::getCreatedAtColumnName() => [
                ['not_empty'],
                ['date'],
            ],
        ];

        if ($this->isCreatedByRequired()) {
            $rules += [
                static::getCreatedByColumnName() => [
                    ['not_empty'],
                ],
            ];
        }

        return $rules;
    }


    /**
     * @inheritDoc
     */
    public function setCreatedBy(UserInterface $user): CreatedByAtInterface
    {
        $this->set(self::REL_CREATED_BY, $user);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedBy(): UserInterface
    {
        return $this->get(self::REL_CREATED_BY);
    }

    /**
     * @inheritDoc
     */
    public function isCreatedBy(UserInterface $user): bool
    {
        return $this->getCreatedBy()->isSameAs($user);
    }

    public static function getCreatedByColumnName(): string
    {
        return 'created_by';
    }

    protected function isCreatedByRequired(): bool
    {
        return true;
    }
}
