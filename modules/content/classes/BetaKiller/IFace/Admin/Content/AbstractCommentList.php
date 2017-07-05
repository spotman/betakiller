<?php
namespace BetaKiller\IFace\Admin\Content;

abstract class AbstractCommentList extends AbstractAdminBase
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        $comments = $this->get_comments_list();

        $data = [];

        foreach ($comments as $comment) {
            $data[] = $this->get_comment_data($comment);
        }

        return [
            'comments' => $data,
        ];
    }

    /**
     * @return \BetaKiller\Model\ContentComment[]
     */
    abstract protected function get_comments_list();

    protected function get_comment_data(\BetaKiller\Model\ContentComment $comment)
    {
        $status = $comment->get_current_status();

        return [
            'id'            =>  $comment->get_id(),
            'publicURL'     =>  $comment->getPublicReadUrl($this->ifaceHelper), // Get public URL via related model
            'editURL'       =>  $this->ifaceHelper->getReadEntityUrl($comment), // Get admin URL via related model
            'contentLabel'  =>  $comment->getRelatedContentLabel(),
            'author'        =>  [
                'isGuest'   =>  $comment->author_is_guest(),
                'name'      =>  $comment->get_author_name(),
                'email'     =>  $comment->get_author_email(),
                'ip'        =>  $comment->get_ip_address(),
                'agent'     =>  $comment->get_user_agent(),
            ],
            'status'        =>  [
                'id'            =>  $status->get_id(),
                'codename'      =>  $status->get_codename(),
                'transitions'   =>  $status->get_allowed_target_transitions_codename_array(),
            ],
            'message'       =>  $comment->get_message(),
            'preview'       =>  \Text::limit_chars($comment->get_message(), 300, null, true),
        ];
    }
}
