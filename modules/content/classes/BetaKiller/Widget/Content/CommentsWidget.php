<?php
namespace BetaKiller\Widget\Content;

use BetaKiller\Exception\ValidationException;
use BetaKiller\IdentityConverterInterface;
use BetaKiller\Model\ContentComment;
use BetaKiller\Repository\ContentCommentRepository;
use BetaKiller\Repository\EntityRepository;
use BetaKiller\Widget\AbstractPublicWidget;
use BetaKiller\Widget\WidgetException;
use BetaKiller\Workflow\ContentCommentWorkflow;
use HTML;
use Psr\Http\Message\ServerRequestInterface;
use Valid;
use Validation;

final class CommentsWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Repository\ContentCommentRepository
     */
    private $commentRepository;

    /**
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepository;

    /**
     * @var \BetaKiller\IdentityConverterInterface
     */
    private $converter;

    /**
     * CommentsWidget constructor.
     *
     * @param \BetaKiller\Repository\ContentCommentRepository $commentRepository
     * @param \BetaKiller\Repository\EntityRepository         $entityRepository
     * @param \BetaKiller\Workflow\ContentCommentWorkflow     $workflow
     * @param \BetaKiller\IdentityConverterInterface          $converter
     */
    public function __construct(
        ContentCommentRepository                $commentRepository,
        EntityRepository                        $entityRepository,
        private readonly ContentCommentWorkflow $workflow,
        IdentityConverterInterface              $converter
    ) {
        $this->commentRepository = $commentRepository;
        $this->entityRepository  = $entityRepository;
        $this->converter         = $converter;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Widget\WidgetException
     * @throws \Kohana_Exception
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $entitySlug   = (string)$context['entity'];
        $entityItemId = (int)$context['entityItemId'];

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
            $createdAt   = $comment->getCreatedAt();
            $email       = $comment->getAuthorEmail();
            $parentModel = $comment->getParent();
            $parentID    = $parentModel ? $parentModel->getID() : 0;

            $commentsData[] = [
                'id'        => $comment->getID(),
                'parent_id' => $parentID,
                'date'      => $createdAt->format('d.m.Y'),
                'time'      => $createdAt->format('H:i:s'),
                'name'      => $comment->getAuthorName(),
                'email'     => $email,
                'message'   => $comment->getMessage(),
                'image'     => 'https://1.gravatar.com/avatar/'.md5($email).'?s=100&d=identicon&r=g',
                'level'     => $comment->getLevel(),
            ];
        }

        return [
            'comments'    => $commentsData,
            'form_action' => '#', // TODO
            'token'       => \md5(\microtime()),// TODO Security::token(),
        ];
    }

    /**
     * @throws \Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Widget\WidgetException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    public function actionAdd()
    {
        if ($this->request->is_ajax()) {
            $this->response->contentTypeJson();
        }

        $entitySlug   = $this->request->post('entity');
        $entityItemId = $this->request->post('entityItemId');

        if (!$entitySlug) {
            throw new WidgetException('[entity] must be provided via request');
        }

        if (!$entityItemId) {
            throw new WidgetException('[entityItemId] must be provided via request');
        }

        $entity = $this->entityRepository->findBySlug($entitySlug);

        // Validation
        $validation = Validation::factory($this->request->post());

        $validation
            ->rule('csrf-key', [Valid::class, 'not_empty'])
            ->rule('csrf-key', [Security::class, 'check']);

        if (!$validation->check()) {
            $errors = $this->getValidationErrors($validation);
            $this->response->sendErrorJson($errors);

            return;
        }

        $name      = HTML::chars($this->request->post('name'));
        $email     = $this->request->post('email');
        $message   = HTML::chars($this->request->post('message'));
        $ipAddress = HTML::chars($this->request->getClientIp());
        $agent     = HTML::chars($this->request->getUserAgent());
        $parentID  = $this->request->post('parent');

        $parentID = $parentID ? $this->converter->decode(ContentComment::getModelName(), $parentID) : null;

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

        $this->workflow->draft($model);

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

            $this->response->sendSuccessJson();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (ValidationException $e) {
            $this->response->sendErrorJson($e->getFirstItem()->getMessage());
        }
    }

    private function getValidationErrors(Validation $validation): array
    {
        return $validation->errors($this->getValidationMessagesPath());
    }

    private function getValidationMessagesPath(): string
    {
        return 'widgets'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $this->getName());
    }
}
