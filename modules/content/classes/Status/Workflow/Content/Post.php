<?php

class Status_Workflow_Content_Post extends Status_Workflow
{
    const TRANSITION_PUBLISH = 'publish';
    const TRANSITION_PAUSE = 'pause';

    public function draft()
    {
        if ($this->model()->has_current_status()) {
            throw new Status_Workflow_Exception('Can not mark post [:id] as draft coz it is in [:status] status', [
                ':id'       =>  $this->model()->get_id(),
                ':status'   =>  $this->model()->get_current_status()->get_codename()
            ]);
        }

        $this->model()->set_start_status();
    }

    public function publish()
    {
        $this->make_uri();

        $this->do_transition(self::TRANSITION_PUBLISH);
    }

    public function pause()
    {
        $this->do_transition(self::TRANSITION_PAUSE);
    }

    protected function make_uri()
    {
        // Nothing to do if uri was already set
        if ($this->model()->get_uri()) {
            return;
        }

        $label = $this->model()->get_label();

        if (!$label) {
            throw new Status_Workflow_Exception('Post [:id] must have uri or label before publishing', [':id' => $this->model()->get_id()]);
        }

        $uri = URL::transliterate($label);

        // Saving uri
        $this->model()->set_uri($uri);
    }

    /**
     * @return \Model_ContentPost|\Status_Related_Model
     */
    protected function model()
    {
        return $this->model;
    }
}
