<?php

use BetaKiller\Model\GroupInterface;

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
    'groups' => [
        GroupInterface::GROUP_CODENAME1 => [
            \BetaKiller\Model\RoleInterface::GUEST_ROLE_NAME,
        ],
        GroupInterface::GROUP_CODENAME2 => [
            \BetaKiller\Model\RoleInterface::LOGIN_ROLE_NAME,
        ],
        GroupInterface::GROUP_CODENAME3 => [
            \BetaKiller\Model\RoleInterface::EMPLOYER_ROLE_NAME,
            \BetaKiller\Model\RoleInterface::APPLICANT_ROLE_NAME,
            \BetaKiller\Model\RoleInterface::SCOUT_ROLE_NAME,
        ],
        GroupInterface::GROUP_CODENAME4 => [
            \BetaKiller\Model\RoleInterface::MODERATOR_ROLE_NAME,
            \BetaKiller\Model\RoleInterface::ADMIN_ROLE_NAME,
        ],
        GroupInterface::GROUP_CODENAME5 => [
            \BetaKiller\Model\RoleInterface::DEVELOPER_ROLE_NAME,
            \BetaKiller\Model\RoleInterface::ROOT_ROLE_NAME,
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
        'message/codename/1' => [
            'group' => GroupInterface::GROUP_CODENAME1,
        ],
        'message/codename/2' => [
            'group' => GroupInterface::GROUP_CODENAME2,
        ],
        'message/codename/3' => [
            'group' => GroupInterface::GROUP_CODENAME3,
        ],
        'message/codename/4' => [
            'group' => GroupInterface::GROUP_CODENAME4,
        ],
        'message/codename/5' => [
            'group' => GroupInterface::GROUP_CODENAME5,
        ],
    ],
];
