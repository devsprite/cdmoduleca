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

require_once(dirname(__FILE__) . '/../../classes/HistoStatsMainClass.php');

class AdminHistoriqueController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'cdmoduleca';
        $this->className = 'HistoStatsMainClass';
        $this->table = 'histostatsmain';
        $this->identifier = 'id_histostatsmain';
        $this->_orderBy = 'datepickerFrom';
        $this->_orderWay = 'DESC';
        $this->bootstrap = true;
        $this->lang = false;
        $this->list_no_link = true;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->original_filter = '';
//        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/appel/';
//        $this->_select = 'a.*, CONCAT(lastname, " - ", firstname) as name';
//        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee` ';

        $this->fields_list = array(
            'id_histostatsmain' => array(
                'title' => 'ID',
                'callback' => 'buttonView'
            ),
            'datepickerFrom' => array(
                'title' => 'Du',
                'filter_key' => 'a!datepickerFrom'
            ),
            'datepickerTo' => array(
                'title' => 'Au',
                'filter_key' => 'a!datepickerTo'
            ),
            'filterCoach' => array(
                'title' => 'Coach',
                'filter_key' => 'a!filterCoach'
            ),
            'caAjuste' => array(
                'title' => 'CA',
                'filter_key' => 'a!caAjuste',
                'callback' => 'formatNumber'
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
        $this->tpl_view_vars['histostatsmain'] = $this->loadObject();
        return parent::renderView();
    }

    public function formatNumber($id, $params)
    {
        return $id . ' €';
    }

    public function buttonView($id, $params)
    {
        $tokenLite = Tools::getAdminTokenLite('AdminCaLetSens');
        $link = self::$currentIndex . '&controller=AdminCaLetSens&id_histo=' . $id . '&token=' . $tokenLite;
        $html = '<a href="' . $link . '" class="btn btn-info">Voir</a>';
        return $html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deletehistostatsmain')) {
            $id = (int)Tools::getValue('id_histostatsmain');
            if (!DB::getInstance()->delete('histostatsmain', 'id_histostatsmain = ' . $id) ||
                !DB::getInstance()->delete('histoajoutsomme', 'id_histostatsmain = ' . $id) ||
                !DB::getInstance()->delete('histoobjectifcoach', 'id_histostatsmain = ' . $id) ||
                !DB::getInstance()->delete('histostatstable', 'id_histostatsmain = ' . $id)
            ){
                $this->errors = 'Erreur lors de la suppression';
            } else {
                $this->confirmations = 'Base de donnée mise à jour';
            }
        }
    }
}