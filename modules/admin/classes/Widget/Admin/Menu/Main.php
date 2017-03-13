<?php

class Widget_Admin_Menu_Main extends \BetaKiller\IFace\Widget\Admin
{
    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function get_data()
    {
        $ifacesData = [];

        foreach ($this->get_menu_ifaces() as $iface) {
            $ifacesData[] = [
                'url'       =>  $iface->url(),
                'label'     =>  $iface->get_label(),
                'active'    =>  $iface->is_in_stack(),
            ];
        }

        return [
            'items' =>  $ifacesData,
        ];
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface[]
     */
    protected function get_menu_ifaces()
    {
        /** @var BetaKiller\IFace\Admin\Content\PostIndex $posts */
        $posts = $this->iface_from_codename('Admin_Content_PostIndex');

        /** @var BetaKiller\IFace\Admin\Content\CommentIndex $comments */
        $comments = $this->iface_from_codename('Admin_Content_CommentIndex');

        return [
            $posts,
            $comments,
        ];
    }
}
