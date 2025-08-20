<?php

declare(strict_types=1);

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Notification\Message\CommentAuthorApproveMessage;
use BetaKiller\Notification\Message\CommentAuthorReplyMessage;
use BetaKiller\Notification\Message\ModeratorPostCompleteMessage;
use BetaKiller\Notification\Transport\EmailTransport;

const GROUP_POST_MODERATION    = 'post-moderation';
const GROUP_COMMENT_USER_EVENT = 'comment-user-event';

return [
    /**
     * Notification groups and relation to ACL roles
     *
     * [
     *   groupCodename1:[role_codename1,role_codename2,..],
     *   groupCodename2:[..],
     *   ..
     * ]
     */
    'groups'   => [
        GROUP_POST_MODERATION => [
            'roles' => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
        ],

        GROUP_COMMENT_USER_EVENT => [
            'roles' => [
                // Direct messaging to any user
                RoleInterface::GUEST,
            ],
        ],
    ],

    /**
     * Messages options
     *
     * [
     *   messageCodename1:[
     *     'group':groupCodename,
     *   ],
     *   messageCodename2:[..],
     * ]
     */
    'messages' => [
        ModeratorPostCompleteMessage::getCodename() => [
            NotificationConfig::FQCN      => ModeratorPostCompleteMessage::class,
            NotificationConfig::GROUP     => GROUP_POST_MODERATION,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],

        CommentAuthorApproveMessage::getCodename() => [
            NotificationConfig::FQCN      => CommentAuthorApproveMessage::class,
            NotificationConfig::GROUP     => GROUP_COMMENT_USER_EVENT,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],

        CommentAuthorReplyMessage::getCodename() => [
            NotificationConfig::FQCN      => CommentAuthorReplyMessage::class,
            NotificationConfig::GROUP     => GROUP_COMMENT_USER_EVENT,
            NotificationConfig::TRANSPORT => EmailTransport::getName(),
        ],
    ],
];
