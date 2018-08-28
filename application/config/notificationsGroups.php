<?php

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
        'groupCodename1' => [
            'guest',
        ],
        'groupCodename2' => [
            'login',
        ],
        'groupCodename3' => [
            'employer',
            'applicant',
            'scout',
        ],
        'groupCodename4' => [
            'moderator',
            'admin',
        ],
        'groupCodename5' => [
            'developer',
            'root',
        ],
    ],
];
