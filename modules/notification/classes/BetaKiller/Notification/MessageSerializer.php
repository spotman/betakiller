<?php

declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;

class MessageSerializer
{
    private const KEY_NAME        = 'name';
    private const KEY_TRANSPORT   = 'transport';
    private const KEY_CRITICAL    = 'critical';
    private const KEY_FROM        = 'from';
    private const KEY_TARGET      = 'target';
    private const KEY_DATA        = 'data';
    private const KEY_ACTION_URL  = 'action';
    private const KEY_ATTACHMENTS = 'attachments';

    private const KEY_TARGET_TYPE = 'type';

    private const KEY_USER_ID       = 'id';
    private const KEY_EMAIL_ADDRESS = 'address';
    private const KEY_EMAIL_NAME    = 'name';
    private const KEY_EMAIL_LANG    = 'lang';

    private const KEY_PHONE_NUMBER = 'phone';
    private const KEY_PHONE_LANG   = 'lang';

    private const TARGET_TYPE_USER  = 'user';
    private const TARGET_TYPE_EMAIL = 'email';
    private const TARGET_TYPE_PHONE = 'phone';

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
            self::KEY_TRANSPORT   => $message->getTransportName(),
            self::KEY_CRITICAL    => $message->isCritical(),
            self::KEY_DATA        => $message->getTemplateData(),
            self::KEY_ATTACHMENTS => $message->getAttachments(),
            self::KEY_ACTION_URL  => $message->hasActionUrl() ? $message->getActionUrl() : null,
        ];

        return json_encode($data);
    }

    public function unserialize(string $packed): MessageInterface
    {
        $data = json_decode($packed, false);

        $name       = $data->{self::KEY_NAME};
        $transport  = $data->{self::KEY_TRANSPORT};
        $target     = $this->unserializeTarget((array)$data->{self::KEY_TARGET});
        $isCritical = (bool)$data->{self::KEY_CRITICAL};

        $message = new Message($name, $target, $transport, $isCritical);

        $message->setTemplateData((array)$data->{self::KEY_DATA});

        if ($data->{self::KEY_FROM}) {
            $message->setFrom($data->{self::KEY_FROM});
        }

        if ($data->{self::KEY_ATTACHMENTS}) {
            foreach ($data->{self::KEY_ATTACHMENTS} as $attach) {
                $message->addAttachment($attach);
            }
        }

        if ($data->{self::KEY_ACTION_URL}) {
            $message->setActionUrl($data->{self::KEY_ACTION_URL});
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

            case $target instanceof EmailMessageTargetInterface:
                return [
                    self::KEY_TARGET_TYPE   => self::TARGET_TYPE_EMAIL,
                    self::KEY_EMAIL_ADDRESS => $target->getMessageEmail(),
                    self::KEY_EMAIL_NAME    => $target->getFullName(),
                    self::KEY_EMAIL_LANG    => $target->getLanguageIsoCode(),
                ];

            case $target instanceof PhoneMessageTargetInterface:
                return [
                    self::KEY_TARGET_TYPE  => self::TARGET_TYPE_PHONE,
                    self::KEY_PHONE_NUMBER => $target->getMessagePhone(),
                    self::KEY_PHONE_LANG   => $target->getLanguageIsoCode(),
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
                return new EmailMessageTarget(
                    $data[self::KEY_EMAIL_ADDRESS],
                    $data[self::KEY_EMAIL_NAME],
                    $data[self::KEY_EMAIL_LANG]
                );

            case self::TARGET_TYPE_PHONE:
                return new PhoneMessageTarget(
                    $data[self::KEY_PHONE_NUMBER],
                    $data[self::KEY_PHONE_LANG]
                );

            default:
                throw new Exception('Unknown target type ":name"', [
                    ':name' => $type,
                ]);
        }
    }
}
