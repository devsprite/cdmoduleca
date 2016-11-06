<?php

require_once(dirname(__FILE__) . '/../../classes/AppelClass.php');

class AdminAppelController extends ModuleAdminController
{
    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }

        $this->module = 'cdmoduleca';
        $this->className = 'AppelClass';
        $this->table = 'appel';
        $this->identifier = 'id_appel';
        $this->_orderBy = 'date_upd';
        $this->_orderWay = 'DESC';
        $this->bootstrap = true;
        $this->lang = false;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/appel/';
        $this->original_filter = '';
        $this->_select = 'a.*, CONCAT(lastname, " - ", firstname) as name';
        $this->_join = 'LEFT JOIN `ps_employee` AS e ON a.`id_employee` = e.`id_employee` ';

        $this->fields_list = array(
            'id_appel' => array(
                'title' => 'ID',
            ),
            'id_employee' => array(
                'title' => 'Id_employé',
            ),
            'name' => array(
                'title' => 'Employé',
            ),
            'compteur' => array(
                'title' => 'Compteur',
            ),
            'date_upd' => array(
                'title' => 'Date',
            )
        );

        parent::__construct();
    }


    public function renderList()
    {
        if (isset($this->_filter) && trim($this->_filter) == '')
            $this->_filter = $this->original_filter;

        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderView()
    {
        $this->tpl_view_vars['appel'] = $this->loadObject();
        return parent::renderView();
    }

}