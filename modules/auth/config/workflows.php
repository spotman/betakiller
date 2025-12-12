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
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_CHECK  => UserState::PENDING,
                    UserWorkflow::TRANSITION_BAN    => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE => UserState::REMOVED,
                ],
            ],

            UserState::PENDING => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_APPROVE => UserState::APPROVED,
                    UserWorkflow::TRANSITION_REJECT  => UserState::REJECTED,
                    UserWorkflow::TRANSITION_BAN     => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE  => UserState::REMOVED,
                ],
            ],

            UserState::REJECTED => [
                WorkflowConfig::ACTIONS => [
                    UserResource::ACTION_READ   => [
                        RoleInterface::USER_MANAGEMENT,
                    ],
                    UserResource::ACTION_UPDATE => [
                        RoleInterface::LOGIN,
                    ],
                ],

                WorkflowConfig::TRANSITIONS => [
                    UserWorkflow::TRANSITION_APPROVE => UserState::APPROVED,
                    UserWorkflow::TRANSITION_BAN     => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE  => UserState::REMOVED,
                ],
            ],

            UserState::APPROVED => [
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
                    // Additional checks are required for resumed accounts
                    UserWorkflow::TRANSITION_RESUME => UserState::PENDING,
                    UserWorkflow::TRANSITION_BAN    => UserState::BANNED,
                    UserWorkflow::TRANSITION_REMOVE => UserState::REMOVED,
                ],
            ],

            UserState::BANNED => [
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
                    UserWorkflow::TRANSITION_UNBAN  => UserState::APPROVED,
                    UserWorkflow::TRANSITION_REMOVE => UserState::REMOVED,
                ],
            ],

            UserState::REMOVED => [
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
            UserWorkflow::TRANSITION_CHECK => [
                // Self-service via auto-approve
                RoleInterface::LOGIN,
            ],

            UserWorkflow::TRANSITION_APPROVE => [
                // Self-service via auto-approve
                RoleInterface::LOGIN,
                // Approved by moderator
                RoleInterface::USER_MANAGEMENT,
            ],

            UserWorkflow::TRANSITION_REJECT => [
                // Rejected by moderator
                RoleInterface::USER_MANAGEMENT,
            ],

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
