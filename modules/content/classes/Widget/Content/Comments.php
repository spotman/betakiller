<?php

use BetaKiller\Helper\ContentTrait;
use BetaKiller\IFace\Widget;
use BetaKiller\IFace\Widget\Exception;

class Widget_Content_Comments extends Widget
{
    use ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws Exception
     */
    public function get_data()
    {
        $entitySlug = $this->getContextParam('entity');
        $entityItemId = (int) $this->getContextParam('entityItemId');

        if (!$entitySlug) {
            throw new Exception('[entity] must be provided via widget context');
        }

        if (!$entityItemId) {
            throw new Exception('[entity_item_id] must be provided via widget context');
        }

        $entity = $this->model_factory_content_entity()->find_by_slug($entitySlug);

        $comments = $this->model_factory_content_comment()->get_entity_item_comments($entity, $entityItemId);

        $commentsData = [];

        foreach ($comments as $comment) {
            $created_at = $comment->get_created_at();

            $email = $comment->get_author_email();

            $commentsData[] = [
                'date'      =>  $created_at->format('d.m.Y'),
                'time'      =>  $created_at->format('H:i:s'),
                'name'      =>  $comment->get_author_name(),
                'email'     =>  $email,
                'message'   =>  $comment->get_message(),
                'image'     =>  'http://1.gravatar.com/avatar/'.md5($email).'?s=100&d=identicon&r=g',
            ];
        }

        return [
            'comments'      =>  $commentsData,
            'form_action'   =>  $this->url('add'),
            'token'         =>  Security::token(),
        ];
    }

    public function action_add()
    {
        if ($this->is_ajax()) {
            $this->content_type_json();
        }

        $entitySlug = $this->post('entity');
        $entityItemId = $this->post('entityItemId');

        if (!$entitySlug) {
            throw new Exception('[entity] must be provided via request');
        }

        if (!$entityItemId) {
            throw new Exception('[entityItemId] must be provided via request');
        }

        $entity = $this->model_factory_content_entity()->find_by_slug($entitySlug);

        // Validation
        $validation = Validation::factory($this->post());

        $validation
            ->rule('csrf-key', 'not_empty')
            ->rule('csrf-key', 'Security::check');

        if ( !$validation->check() ) {
            $errors = $this->get_validation_errors($validation);
            $this->send_error_json($errors);
            return;
        }

        $name       = HTML::chars($this->post('name'));
        $email      = $this->post('email');
        $message    = HTML::chars($this->post('message'));
        $ipAddress  = HTML::chars($this->getRequest()->client_ip());

        // TODO throttling

        $model = $this->model_factory_content_comment();

        try {
            $model
                ->set_entity($entity)
                ->set_entity_item_id($entityItemId)
                ->set_guest_author_name($name)
                ->set_guest_author_email($email)
                ->set_message($message)
                ->set_ip_address($ipAddress)
                ->mark_as_pending();

            $model->save();

            $this->send_success_json();
        } catch (ORM_Validation_Exception $e) {
            $errors = $this->get_validation_errors($model->validation());

            $this->send_error_json(array_pop($errors));
        }
    }
}
