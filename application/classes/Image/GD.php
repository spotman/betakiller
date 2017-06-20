<?php

class Image_GD extends Kohana_Image_GD
{
    /**
     * Execute a render.
     *
     * @param   string  $type image type: png, jpg, gif, etc
     * @param   integer $quality quality
     *
     * @return  string
     */
    protected function _do_render($type, $quality)
    {
        $this->process_interlacing();

        return parent::_do_render($type, $quality);
    }

    protected function process_interlacing()
    {
        // Enabling interlace mode for JPEG
        if ($this->mime === 'image/jpeg') {
            imageinterlace($this->_image, 1);
        }
    }

    /**
     * Execute a save.
     *
     * @param   string  $file new image filename
     * @param   integer $quality quality
     *
     * @return  boolean
     */
    protected function _do_save($file, $quality)
    {
        $this->process_interlacing();

        return parent::_do_save($file, $quality);
    }
}
