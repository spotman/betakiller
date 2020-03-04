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
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_REG_CLAIM       => UserState::CLAIMED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::BLOCKED,
                ],
            ],

            UserState::EMAIL_CONFIRMED => [
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
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_SUSPEND         => UserState::SUSPENDED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::BLOCKED,
                ],
            ],

            UserState::EMAIL_CHANGED => [
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
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::EMAIL_CONFIRMED,
                    // Allow redo just in case of user mistake
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_SUSPEND         => UserState::SUSPENDED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::BLOCKED,
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
                    UserWorkflow::TRANSITION_RESUME_SUSPENDED => UserState::RESUMED,
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
                    UserWorkflow::TRANSITION_CHANGE_EMAIL    => UserState::EMAIL_CHANGED,
                    UserWorkflow::TRANSITION_EMAIL_CONFIRMED => UserState::EMAIL_CONFIRMED,
                    UserWorkflow::TRANSITION_BLOCK           => UserState::BLOCKED,
                ],
            ],

            UserState::BLOCKED => [
                WorkflowConfig::IS_FINISH => true,

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
                WorkflowConfig::TRANSITIONS => [],
            ],

            UserState::CLAIMED => [
                WorkflowConfig::IS_FINISH => true,

                WorkflowConfig::ACTIONS     => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::USER_MANAGEMENT,
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

            UserWorkflow::TRANSITION_RESUME_SUSPENDED => [
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_BLOCK => [
                RoleInterface::USER_MANAGEMENT,
            ],
        ],
    ],
];
