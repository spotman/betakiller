<?php
declare(strict_types=1);

use BetaKiller\Model\NotificationGroupInterface;

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

    ],
];
