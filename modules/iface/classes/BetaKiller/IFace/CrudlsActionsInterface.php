<?php
namespace BetaKiller\IFace;

interface CrudlsActionsInterface
{
    public const ACTION_CREATE = 'create';
    public const ACTION_READ   = 'read';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LIST   = 'list';
    public const ACTION_SEARCH = 'search';
}
