<?php namespace BetaKiller\Assets;

use BetaKiller\Assets\Storage\AssetsStorageCfs;
use BetaKiller\Assets\Storage\AssetsStorageLocal;

class AssetsStorageFactory
{
    /**
     * @var AssetsStorageFactory
     */
    protected static $_instance;

    /**
     * @return \BetaKiller\Assets\AssetsStorageFactory
     * @deprecated Use DI instead
     */
    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    /**
     * @param string $codename
     *
     * @return AssetsStorageLocal|AssetsStorageCfs
     * @throws AssetsStorageException
     * @todo Rewrite to NamespaceBasedFactory
     */
    public function create($codename)
    {
        $class_name = 'BetaKiller\\Assets\\Storage\\AssetsStorage'.$codename;

        if (!class_exists($class_name)) {
            throw new AssetsStorageException('Unknown storage :class', [':class' => $class_name]);
        }

        return new $class_name;
    }
}
