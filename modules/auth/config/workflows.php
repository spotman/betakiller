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
            UserState::CREATED => [
                WorkflowConfig::IS_START => true,

                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_SUSPEND => UserState::SUSPENDED,
                    UserWorkflow::TRANSITION_BAN     => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE  => UserState::REMOVED,
                ],
            ],

            UserState::SUSPENDED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    // Set "resumed" state to prevent workflow hacks (created => suspended => confirmed)
                    UserWorkflow::TRANSITION_RESUME => UserState::RESUMED,
                    UserWorkflow::TRANSITION_BAN    => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE => UserState::REMOVED,
                ],
            ],

            UserState::RESUMED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::LOGIN,
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_SUSPEND => UserState::SUSPENDED,
                    UserWorkflow::TRANSITION_BAN     => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE  => UserState::REMOVED,
                ],
            ],

            UserState::BANNED => [
//                WorkflowConfig::IS_FINISH => true,

                WorkflowConfig::ACTIONS     => [
                    UserResource::ACTION_READ   => [
//                        RoleInterface::LOGIN,
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                // No transitions for blocked Users
                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_UNBAN  => UserState::RESUMED,
                    UserWorkflow::TRANSITION_REMOVE => UserState::REMOVED,
                ],
            ],

            UserState::REMOVED => [
//                WorkflowConfig::IS_FINISH => true,

                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
//                        RoleInterface::LOGIN,
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        // No one can update
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_RESTORE => UserState::CREATED,
                ],
            ],
        ],

        WorkflowConfig::TRANSITIONS => [
            UserWorkflow::TRANSITION_SUSPEND => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_RESUME => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_RESTORE => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_REMOVE => [
                RoleInterface::LOGIN,
                RoleInterface::USER_MANAGEMENT,
            ],

            UserWorkflow::TRANSITION_BAN => [
                RoleInterface::USER_MANAGEMENT,
            ],

            UserWorkflow::TRANSITION_UNBAN => [
                RoleInterface::USER_MANAGEMENT,
            ],
        ],
    ],
];
