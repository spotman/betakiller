<?php

// TODO Refactoring after Model_AdminContentFile
class Model_AdminYoutubeRecord extends Model_AdminContentFile
{

//    /**
//     * Rule definitions for validation
//     *
//     * @return array
//     */
//    public function rules()
//    {
//        return parent::rules() + [
//            'alt'   =>  [
//                ['not_empty']
//            ],
//        ];
//    }

    public function get_youtube_embed_url()
    {
        // TODO make youtube full url
        return 'https://www.youtube.com/embed/'.$this->get_youtube_id();
    }

    /**
     * @param int $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_youtube_id($value)
    {
        return $this->set('youtube_id', (int) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_youtube_id()
    {
        return $this->get('youtube_id');
    }

    /**
     * @param int $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_width($value)
    {
        return $this->set('width', (int) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_width()
    {
        return $this->get('width');
    }

    /**
     * @param int $value
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_height($value)
    {
        return $this->set('height', (int) $value);
    }

    /**
     * @return string
     * @throws Kohana_Exception
     */
    public function get_height()
    {
        return $this->get('height');
    }
}
