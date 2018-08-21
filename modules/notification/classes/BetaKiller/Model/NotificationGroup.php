<?php
declare(strict_types=1);
namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationException;

class NotificationGroup extends \ORM implements NotificationGroupInterface
{
    public const TABLE_NAME              = 'notification_group';
    public const TABLE_FIELD_CODENAME    = 'codename';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'language' => [
                'model'       => 'Language',
                'foreign_key' => 'language_id',
            ],
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            'codename'    => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            'description' => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    /**
     * @return array[string self::TABLE_FIELD_CODENAME, string self::TABLE_FIELD_DESCRIPTION]
     */
    public function getAll(): array
    {
        return [
            self::TABLE_FIELD_CODENAME    => $this->getCodename(),
            self::TABLE_FIELD_DESCRIPTION => $this->getDescription(),
        ];
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get(self::TABLE_FIELD_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setCodename(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        if ($value === '') {
            throw new NotificationException('Codename cant not be empty');
        }
        $this->set(self::TABLE_FIELD_CODENAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::TABLE_FIELD_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setDescription(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_DESCRIPTION, $value);

        return $this;
    }
}
