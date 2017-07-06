<?php

use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class Widget_Content_Comments extends AbstractBaseWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentCommentRepository
     */
    private $commentRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepository;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws WidgetException
     */
    public function getData(): array
    {
        $entitySlug   = $this->getContextParam('entity');
        $entityItemId = (int)$this->getContextParam('entityItemId');

        if (!$entitySlug) {
            throw new WidgetException('[entity] must be provided via widget context');
        }

        if (!$entityItemId) {
            throw new WidgetException('[entity_item_id] must be provided via widget context');
        }

        $entity = $this->entityRepository->findBySlug($entitySlug);

        $comments = $this->commentRepository->getEntityItemApprovedComments($entity, $entityItemId);

        $commentsData = [];

        foreach ($comments as $comment) {
            $created_at  = $comment->get_created_at();
            $email       = $comment->get_author_email();
            $parentModel = $comment->getParent();
            $parentID    = $parentModel ? $parentModel->get_id() : 0;

            $commentsData[] = [
                'id'        => $comment->get_id(),
                'parent_id' => $parentID,
                'date'      => $created_at->format('d.m.Y'),
                'time'      => $created_at->format('H:i:s'),
                'name'      => $comment->get_author_name(),
                'email'     => $email,
                'message'   => $comment->get_message(),
                'image'     => 'https://1.gravatar.com/avatar/'.md5($email).'?s=100&d=identicon&r=g',
                'level'     => $comment->get_level(),
            ];
        }

        return [
            'comments'    => $commentsData,
            'form_action' => $this->url('add'),
            'token'       => Security::token(),
        ];
    }

    public function action_add()
    {
        if ($this->is_ajax()) {
            $this->content_type_json();
        }

        $entitySlug   = $this->post('entity');
        $entityItemId = $this->post('entityItemId');

        if (!$entitySlug) {
            throw new WidgetException('[entity] must be provided via request');
        }

        if (!$entityItemId) {
            throw new WidgetException('[entityItemId] must be provided via request');
        }

        $entity = $this->entityRepository->findBySlug($entitySlug);

        // Validation
        $validation = Validation::factory($this->post());

        $validation
            ->rule('csrf-key', 'not_empty')
            ->rule('csrf-key', [Security::class, 'check']);

        if (!$validation->check()) {
            $errors = $this->get_validation_errors($validation);
            $this->send_error_json($errors);

            return;
        }

        $name      = HTML::chars($this->post('name'));
        $email     = $this->post('email');
        $message   = HTML::chars($this->post('message'));
        $ipAddress = HTML::chars($this->getRequest()->client_ip());
        $agent     = HTML::chars($this->getRequest()->get_user_agent());
        $parentID  = (int)$this->post('parent');

        /** @var \BetaKiller\Model\ContentComment|null $parentModel */
        $parentModel = $parentID ? $this->commentRepository->findById($parentID) : null;

        // Check parent comment
        if ($parentModel) {
            $parentEntity       = $parentModel->getEntity();
            $parentEntityItemID = $parentModel->getEntityItemID();

            // Check parent comment entity id
            if (!$parentEntity->isEqualTo($entity)) {
                throw new WidgetException('Incorrect parent comment entity; :sent sent instead of :needed', [
                    ':needed' => $entity->get_id(),
                    ':sent'   => $parentEntity->getID(),
                ]);
            }

            // Check parent comment entity item id
            if ($parentEntityItemID !== $entityItemId) {
                throw new WidgetException('Incorrect parent comment entity item id; :sent sent instead of :needed', [
                    ':needed' => $entityItemId,
                    ':sent'   => $parentEntityItemID,
                ]);
            }
        }

        // Throttling
        $commentsCount = $this->commentRepository->getCommentsCountForIP($ipAddress);

        if ($commentsCount > 5) {
            throw new WidgetException('Throttling enabled for IP :ip', [':ip' => $ipAddress]);
        }

        $user  = $this->user;
        $model = $this->commentRepository->create();

        $model->draft();

        // Linking comment to entity and entity item
        $model
            ->setEntity($entity)
            ->setEntityItemID($entityItemId);

        if (!$user->isGuest()) {
            $model->set_author_user($user);
        } else {
            $model->set_guest_author_name($name)->set_guest_author_email($email);
        }

        // Parent comment
        if ($parentModel) {
            $model->setParent($parentModel);
        }

        $model
            ->set_ip_address($ipAddress)
            ->set_user_agent($agent)
            ->set_message($message)
            ->set_created_at();

        try {
            // Saving comment and getting ID
            $this->commentRepository->save($model);

            $model->reload();

            // Force approving if enabled (developers, moderators, etc)
            if ($user && $model->isApproveAllowed()) {
                $model->approve();
                $this->commentRepository->save($model);
            }

            $this->send_success_json();
        } catch (ORM_Validation_Exception $e) {
            $errors = $this->get_validation_errors($model->validation());

            $this->send_error_json(array_pop($errors));
        }
    }
}
