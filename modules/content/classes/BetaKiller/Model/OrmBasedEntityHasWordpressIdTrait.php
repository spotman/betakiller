<?php
namespace BetaKiller\Model;

trait OrmBasedEntityHasWordpressIdTrait
{
    /**
     * @param int $value
     */
    public function setWpId(int $value): void
    {
        $this->set('wp_id', (int)$value);
    }

    /**
     * @return int|null
     */
    public function getWpId(): ?int
    {
        return $this->get('wp_id');
    }
}
