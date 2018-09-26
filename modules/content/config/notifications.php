<?php
declare(strict_types=1);

use BetaKiller\Model\RoleInterface;
use BetaKiller\Status\ContentCommentWorkflow;
use BetaKiller\Status\ContentPostWorkflow;

define('POST_MODERATION', 'post-moderation');
define('COMMENT_USER_EVENT', 'comment-user-event');

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
        POST_MODERATION => [
            RoleInterface::MODERATOR_ROLE_NAME,
        ],

        COMMENT_USER_EVENT => [
            // No roles here, direct messaging
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
        ContentPostWorkflow::NOTIFICATION_POST_COMPLETE => [
            'group' => POST_MODERATION,
        ],

        ContentCommentWorkflow::NOTIFICATION_AUTHOR_APPROVE => [
            'group' => COMMENT_USER_EVENT,
        ],

        ContentCommentWorkflow::NOTIFICATION_PARENT_REPLY => [
            'group' => COMMENT_USER_EVENT,
        ],
    ],
];
