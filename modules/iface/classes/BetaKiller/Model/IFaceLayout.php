<?php
namespace BetaKiller\Model;

use ORM;

/**
 * Class IFaceLayout
 *
 * @category   Models
 * @author     Spotman
 * @package    BetaKiller\IFace
 */
class IFaceLayout extends ORM implements LayoutInterface
{
    protected function configure(): void
    {
        $this->_table_name = 'layouts';

        $this->has_many([
            'iface' => [
                'model'       => 'IFace',
                'foreign_key' => 'layout_id',
            ],
        ]);
    }

    /**
     * Returns TRUE if layout is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool)$this->get('is_default');
    }

    /**
     * Returns layout codename (filename)
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * Returns layout title (human-readable name)
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->get('title');
    }
}
