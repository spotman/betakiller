<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;

class MessageSerializer
{
    private const KEY_NAME        = 'name';
    private const KEY_FROM        = 'from';
    private const KEY_TARGET      = 'target';
    private const KEY_DATA        = 'data';
    private const KEY_ATTACHMENTS = 'attachments';

    private const KEY_TARGET_TYPE = 'type';

    private const KEY_USER_ID       = 'id';
    private const KEY_EMAIL_ADDRESS = 'address';
    private const KEY_EMAIL_NAME    = 'name';
    private const KEY_EMAIL_LANG    = 'lang';

    private const TARGET_TYPE_USER  = 'user';
    private const TARGET_TYPE_EMAIL = 'email';

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * MessageSerializer constructor.
     *
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     */
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function serialize(MessageInterface $message): string
    {
        $data = [
            self::KEY_NAME        => $message->getCodename(),
            self::KEY_FROM        => $message->getFrom(),
            self::KEY_TARGET      => $this->serializeTarget($message->getTarget()),
            self::KEY_DATA        => $message->getTemplateData(),
            self::KEY_ATTACHMENTS => $message->getAttachments(),
        ];

        return json_encode($data);
    }

    public function unserialize(string $packed): MessageInterface
    {
        $data = json_decode($packed, false);

        $message = new Message($data->{self::KEY_NAME});

        $message
            ->setTarget($this->unserializeTarget((array)$data->{self::KEY_TARGET}))
            ->setTemplateData((array)$data->{self::KEY_DATA});

        if ($data->{self::KEY_FROM}) {
            $message->setFrom($data->{self::KEY_FROM});
        }

        if ($data->{self::KEY_ATTACHMENTS}) {
            foreach ($data->{self::KEY_ATTACHMENTS} as $attach) {
                $message->addAttachment($attach);
            }
        }

        return $message;
    }

    private function serializeTarget(MessageTargetInterface $target): array
    {
        switch (true) {
            case $target instanceof UserInterface:
                return [
                    self::KEY_TARGET_TYPE => self::TARGET_TYPE_USER,
                    self::KEY_USER_ID     => $target->getID(),
                ];

            case $target instanceof MessageTargetEmail:
                return [
                    self::KEY_TARGET_TYPE   => self::TARGET_TYPE_EMAIL,
                    self::KEY_EMAIL_ADDRESS => $target->getEmail(),
                    self::KEY_EMAIL_NAME    => $target->getFullName(),
                    self::KEY_EMAIL_LANG    => $target->getLanguageIsoCode(),
                ];

            default:
                throw new Exception('Unknown target type ":class"', [
                    ':class' => get_class($target),
                ]);
        }
    }

    private function unserializeTarget(array $data): MessageTargetInterface
    {
        $type = $data[self::KEY_TARGET_TYPE];

        switch ($type) {
            case self::TARGET_TYPE_USER:
                return $this->userRepo->getById($data[self::KEY_USER_ID]);

            case self::TARGET_TYPE_EMAIL:
                return new MessageTargetEmail(
                    $data[self::KEY_EMAIL_ADDRESS],
                    $data[self::KEY_EMAIL_NAME],
                    $data[self::KEY_EMAIL_LANG]
                );

            default:
                throw new Exception('Unknown target type ":name"', [
                    ':name' => $type,
                ]);
        }
    }
}
