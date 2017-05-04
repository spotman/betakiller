<?php
namespace BetaKiller\Assets\Model;

use Kohana_Exception;
use ORM;

abstract class AbstractAssetsOrmModelSeoImage extends AbstractAssetsOrmImageModel
{
    /**
     * @param string $value
     *
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function setAlt($value)
    {
        return $this->set('alt', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getAlt()
    {
        return $this->get('alt');
    }

    /**
     * @param string $value
     *
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function setTitle($value)
    {
        return $this->set('title', $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function getTitle()
    {
        return $this->get('title');
    }

    public function getAttributesForImgTag($size, array $attributes = [])
    {
        $attributes = array_merge([
            'alt'   => $this->getAlt(),
            'title' => $this->getTitle(),
        ], $attributes);

        return parent::getAttributesForImgTag($size, $attributes);
    }
}
