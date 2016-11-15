<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author  Dominique <dominique@chez-dominique.fr>
 * @copyright   2007-2016 Chez-dominique
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../classes/AppelClass.php');

/**
 * Class AdminAppelController
 * Controller historique des appels
 */
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
        $this->list_no_link = true;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/appel/';
        $this->original_filter = '';
        $this->_select = 'a.*, CONCAT(lastname, " - ", firstname) as name';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee` ';

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
        if (isset($this->_filter) && trim($this->_filter) == '') {
            $this->_filter = $this->original_filter;
        }

        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderView()
    {
        $this->tpl_view_vars['appel'] = $this->loadObject();
        return parent::renderView();
    }
}
