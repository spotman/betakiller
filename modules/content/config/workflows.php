<?php
declare(strict_types=1);

use BetaKiller\Acl\Resource\ContentCommentResource;
use BetaKiller\Acl\Resource\ContentPostResource;
use BetaKiller\Config\WorkflowConfig;
use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentCommentState;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ContentPostState;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Workflow\ContentCommentWorkflow;
use BetaKiller\Workflow\ContentPostWorkflow;

return [
    ContentPost::getModelName() => [
        WorkflowConfig::STATUS_MODEL => ContentPostState::getModelName(),

        WorkflowConfig::STATES => [
            ContentPostState::DRAFT => [
                WorkflowConfig::IS_START => true,

                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentPostResource::ACTION_READ   => [
                        ContentHelper::ROLE_WRITER,
                    ],

                    // Update
                    ContentPostResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_WRITER,
                    ],

                    // Delete
                    ContentPostResource::ACTION_DELETE => [
                        ContentHelper::ROLE_WRITER,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentPostWorkflow::COMPLETE => ContentPostState::PENDING,
                ],
            ],

            ContentPostState::PENDING => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentPostResource::ACTION_READ   => [
                        ContentHelper::ROLE_WRITER,
                    ],

                    // Update
                    ContentPostResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],

                    // Delete
                    ContentPostResource::ACTION_DELETE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentPostWorkflow::PUBLISH => ContentPostState::PUBLISHED,
                    ContentPostWorkflow::FIX     => ContentPostState::FIX_REQUESTED,
                ],
            ],

            ContentPostState::PUBLISHED => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentPostResource::ACTION_READ   => [
                        RoleInterface::GUEST,
                        RoleInterface::LOGIN,
                    ],

                    // Update
                    ContentPostResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentPostWorkflow::PAUSE => ContentPostState::PAUSED,
                ],
            ],

            ContentPostState::FIX_REQUESTED => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentPostResource::ACTION_READ   => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                        ContentHelper::ROLE_WRITER,
                    ],

                    // Update
                    ContentPostResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                        ContentHelper::ROLE_WRITER,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentPostWorkflow::COMPLETE => ContentPostState::PENDING,
                    ContentPostWorkflow::PAUSE    => ContentPostState::PAUSED,
                ],
            ],

            ContentPostState::PAUSED => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentPostResource::ACTION_READ => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                        ContentHelper::ROLE_WRITER,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentPostWorkflow::PUBLISH => ContentPostState::PUBLISHED,
                    ContentPostWorkflow::FIX     => ContentPostState::FIX_REQUESTED,
                ],
            ],
        ],

        WorkflowConfig::TRANSITIONS => [
            ContentPostWorkflow::COMPLETE => [
                ContentHelper::ROLE_WRITER,
            ],

            ContentPostWorkflow::PUBLISH => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            ContentPostWorkflow::FIX => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            ContentPostWorkflow::PAUSE => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
        ],
    ],

    ContentComment::getModelName() => [
        WorkflowConfig::STATUS_MODEL => ContentCommentState::getModelName(),

        WorkflowConfig::STATES => [

            ContentCommentState::PENDING => [
                WorkflowConfig::IS_START => true,

                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentCommentResource::ACTION_READ   => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],

                    // Update
                    ContentCommentResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentCommentWorkflow::APPROVE      => ContentCommentState::APPROVED,
                    ContentCommentWorkflow::REJECT       => ContentCommentState::TRASH,
                    ContentCommentWorkflow::MARK_AS_SPAM => ContentCommentState::SPAM,
                ],
            ],

            ContentCommentState::SPAM => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentCommentResource::ACTION_READ   => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],

                    // Update
                    ContentCommentResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentCommentWorkflow::APPROVE       => ContentCommentState::APPROVED,
                    ContentCommentWorkflow::MOVE_TO_TRASH => ContentCommentState::TRASH,
                ],
            ],

            ContentCommentState::APPROVED => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentCommentResource::ACTION_READ   => [
                        RoleInterface::GUEST,
                        RoleInterface::LOGIN,
                    ],

                    // Update
                    ContentCommentResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentCommentWorkflow::MOVE_TO_TRASH => ContentCommentState::TRASH,
                ],
            ],

            ContentCommentState::TRASH => [
                WorkflowConfig::ACTIONS     => [
                    // Read
                    ContentCommentResource::ACTION_READ   => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],

                    // Update
                    ContentCommentResource::ACTION_UPDATE => [
                        ContentHelper::ROLE_CONTENT_MODERATOR,
                    ],
                ],

                // Target transitions
                WorkflowConfig::TRANSITIONS => [
                    ContentCommentWorkflow::RESTORE_FROM_TRASH => ContentCommentState::PENDING,
                ],
            ],
        ],

        WorkflowConfig::TRANSITIONS => [
            ContentCommentWorkflow::APPROVE            => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
            ContentCommentWorkflow::REJECT             => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
            ContentCommentWorkflow::MARK_AS_SPAM       => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
            ContentCommentWorkflow::MOVE_TO_TRASH      => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
            ContentCommentWorkflow::RESTORE_FROM_TRASH => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
        ],
    ],
];
