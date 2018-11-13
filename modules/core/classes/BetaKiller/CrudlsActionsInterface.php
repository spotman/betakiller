<?php
namespace BetaKiller;

interface CrudlsActionsInterface
{
    public const ACTION_CREATE = 'create';
    public const ACTION_READ   = 'read';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LIST   = 'list';
    public const ACTION_SEARCH = 'search';

    // Create, list and search actions do not require entity model to be set before processing
    public const ACTIONS_WITHOUT_ENTITY = [
        self::ACTION_CREATE,
        self::ACTION_LIST,
        self::ACTION_SEARCH,
    ];
}
