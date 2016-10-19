<?php

class AdminProspectsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'cdmoduleca';
        $this->bootstrap = true;
        $this->className = 'AdminProspects';
        $this->lang = false;
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function init()
    {
        parent::init();

        $this->content = 'Prospects';
    }
}