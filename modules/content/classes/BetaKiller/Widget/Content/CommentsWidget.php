<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Widget\AbstractBaseWidget;
use BetaKiller\Widget\WidgetException;
use HTML;
use ORM_Validation_Exception;
use Security;
use Valid;
use Validation;

class CommentsWidget extends AbstractBaseWidget
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
     * @Inject
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $workflowFactory;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
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
            $created_at = $comment->getCreatedAt();
            $email      = $comment->getAuthorEmail();
            $parentModel = $comment->getParent();
            $parentID    = $parentModel ? $parentModel->getID() : 0;

            $commentsData[] = [
                'id'        => $comment->getID(),
                'parent_id' => $parentID,
                'date'      => $created_at->format('d.m.Y'),
                'time'      => $created_at->format('H:i:s'),
                'name'      => $comment->getAuthorName(),
                'email'     => $email,
                'message'   => $comment->getMessage(),
                'image'     => 'https://1.gravatar.com/avatar/'.md5($email).'?s=100&d=identicon&r=g',
                'level'     => $comment->getLevel(),
            ];
        }

        return [
            'comments'    => $commentsData,
            'form_action' => $this->url('add'),
            'token'       => Security::token(),
        ];
    }

    /**
     * @throws \Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Widget\WidgetException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
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
            ->rule('csrf-key', [Valid::class, 'not_empty'])
            ->rule('csrf-key', [Security::class, 'check']);

        if (!$validation->check()) {
            $errors = $this->getValidationErrors($validation);
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
            if (!$parentEntity->isSameAs($entity)) {
                throw new WidgetException('Incorrect parent comment entity; :sent sent instead of :needed', [
                    ':needed' => $entity->getID(),
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

        /** @var \BetaKiller\Status\ContentCommentWorkflow $workflow */
        $workflow = $this->workflowFactory->create($model);

        $workflow->draft();

        // Linking comment to entity and entity item
        $model
            ->setEntity($entity)
            ->setEntityItemID($entityItemId);

        if (!$user->isGuest()) {
            $model->setAuthorUser($user);
        } else {
            $model->setGuestAuthorName($name)->setGuestAuthorEmail($email);
        }

        // Parent comment
        if ($parentModel) {
            $model->setParent($parentModel);
        }

        $model
            ->setIpAddress($ipAddress)
            ->setUserAgent($agent)
            ->setMessage($message)
            ->setCreatedAt();

        try {
            // Saving comment and getting ID
            $this->commentRepository->save($model);

            // Force approving if enabled (developers, moderators, etc)
            if ($user && $model->isApproveAllowed($this->user)) {
                $workflow->approve();
                $this->commentRepository->save($model);
            }

            $this->send_success_json();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (ORM_Validation_Exception $e) {
            $errors = $this->getValidationErrors($e->getValidationObject());

            $this->send_error_json(array_pop($errors));
        }
    }
}
