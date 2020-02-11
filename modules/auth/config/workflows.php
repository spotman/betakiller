<?php
declare(strict_types=1);

use BetaKiller\Acl\Resource\UserResource;
use BetaKiller\Config\WorkflowConfig;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserState;
use BetaKiller\Workflow\UserWorkflow;

return [
    User::getModelName() => [
        WorkflowConfig::STATUS_MODEL => UserState::getModelName(),

        WorkflowConfig::STATES => [
            UserState::STATE_CREATED => [
                WorkflowConfig::IS_START => true,

                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::STATE_EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::STATE_EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_REG_CLAIM       => UserState::STATE_CLAIMED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::STATE_BLOCKED,
                ],
            ],

            UserState::STATE_EMAIL_CONFIRMED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::STATE_EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::STATE_EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_SUSPEND         => UserState::STATE_SUSPENDED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::STATE_BLOCKED,
                ],
            ],

            UserState::STATE_EMAIL_CHANGED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::STATE_EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::STATE_EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_SUSPEND         => UserState::STATE_SUSPENDED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::STATE_BLOCKED,
                ],
            ],

            UserState::STATE_SUSPENDED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    // Set "created" state to prevent workflow hacks (created => suspended => confirmed)
                    UserWorkflow::TRANSITION_ACTIVATE_SUSPENDED => UserState::STATE_CREATED,
                ],
            ],

            UserState::STATE_BLOCKED => [
                WorkflowConfig::IS_FINISH => true,

                WorkflowConfig::ACTIONS     => [
                    UserResource::ACTION_READ   => [
//                        RoleInterface::LOGIN,
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                // No transitions for blocked Users
                WorkflowConfig::TRANSITIONS => [],
            ],

            UserState::STATE_CLAIMED => [
                WorkflowConfig::IS_FINISH => true,

                WorkflowConfig::ACTIONS     => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::ROLE_USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                // No transitions for claimed Users
                WorkflowConfig::TRANSITIONS => [],
            ],
        ],

        WorkflowConfig::TRANSITIONS => [
            UserWorkflow::TRANSITION_REG_CLAIM => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_EMAIL_CONFIRMED => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_CHANGE_EMAIL => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_SUSPEND => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_ACTIVATE_SUSPENDED => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_BLOCK => [
                RoleInterface::ROLE_USER_MANAGEMENT,
            ],
        ],
    ],
];
