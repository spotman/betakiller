<?php
namespace BetaKiller\IFace\Admin\Content;

use BetaKiller\Model\ContentCommentInterface;

abstract class AbstractCommentList extends AbstractAdminBase
{
    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

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
     * @return \BetaKiller\Model\ContentCommentInterface[]
     */
    abstract protected function get_comments_list();

    protected function get_comment_data(ContentCommentInterface $comment)
    {
        $status = $comment->getCurrentStatus();

        return [
            'id'           => $comment->getID(),
            'publicURL'    => $comment->getPublicReadUrl($this->ifaceHelper), // Get public URL via related model
            'editURL'      => $this->ifaceHelper->getReadEntityUrl($comment), // Get admin URL via related model
            'contentLabel' => $comment->getRelatedContentLabel(),
            'author'       => [
                'isGuest' => $comment->authorIsGuest(),
                'name'    => $comment->getAuthorName(),
                'email'   => $comment->getAuthorEmail(),
                'ip'      => $comment->getIpAddress(),
                'agent'   => $comment->getUserAgent(),
            ],
            'status'       => [
                'id'          => $status->getID(),
                'codename'    => $status->getCodename(),
                'transitions' => $status->getAllowedTargetTransitionsCodenameArray($this->user),
            ],
            'message'      => $comment->getMessage(),
            'preview'      => \Text::limit_chars($comment->getMessage(), 300, null, true),
        ];
    }
}
