<?php
/**
 * 2007-2014 PrestaShop
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
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2016 Chez-Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit();
}


class CdModuleCA extends ModuleGrid
{
    public $errors = array();
    public $html = '';
    public $query;
    public $columns;
    public $default_sort_column;
    public $default_sort_direction;
    public $empty_message;
    public $paging_message;
    public $viewAllCoachs;
    public $idFilterCoach;
    public $idFilterCodeAction;
    public $config = array(
        'CDMODULECA' => '1'
    );


    public function __construct()
    {
        $this->name = 'cdmoduleca';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Dominique';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Module CA');
        $this->description = $this->l('Synthèse CA pour L et Sens');
        $this->viewAllCoachs = array(
            '1' => true,    // SuperAdmin
            '2' => false,   // Logisticien
            '3' => false,   // Traducteur
            '4' => false,   // Commercial
            '5' => false,   // Diet
            '6' => false,   // Stagiaire
            '7' => true,    // Manager
            '8' => false    // WebMarketing
        );
        $this->empty_message = $this->l('Pas d\'enregistrement disponible');
        $this->paging_message = sprintf($this->l('Affichage %1$s de %2$s'), '{0} - {1}', '{2}');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->table_charset = 'utf8';

        $this->default_sort_column = 'total';
        $this->default_sort_direction = 'ASC';

        $this->columns = array(
            array(
                'id' => 'total',
                'header' => $this->l('id'),
                'dataIndex' => 'total',
                'align' => 'center'
            ),

        );
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->alterGroupLangTable() ||
            !$this->alterOrderTable() ||
            !$this->createCodeActionTable() ||
            !$this->updateOrdersTable() ||
            !$this->installConfig() ||
            !$this->registerHook('AdminStatsModules') ||
            !$this->registerHook('ActionValidateOrder')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->removeCodeActionTable() ||
            !$this->alterGroupLangTable('remove') ||
            !$this->alterOrderTable('remove') ||
            !$this->eraseConfig()
        ) {
            return false;
        }

        return true;
    }

    private function alterGroupLangTable($method = 'add')
    {
        if ($method == 'add') {
            $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'group_lang ADD `parrain` VARCHAR (255) NOT NULL DEFAULT 0';
        } else {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'group_lang` DROP COLUMN `parrain`';
        }
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function alterOrderTable($method = 'add')
    {
        if ($method == 'add') {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` ADD `id_code_action` INT (12) NULL,
             ADD `id_employee` INT (12) NULL';
        } else {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` DROP COLUMN `id_code_action`, 
             DROP COLUMN `id_employee`';
        }
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function installConfig()
    {
        foreach ($this->config as $keyname => $value) {
            Configuration::updateValue($keyname, $value);
        }

        return true;
    }

    private function eraseConfig()
    {
        foreach ($this->config as $keyname => $value) {
            Configuration::deleteByName($keyname);
        }

        return true;
    }

    public function getContent()
    {
        $this->postProcess();
        $this->displayForm();

        return $this->html;
    }

    private function postProcess()
    {
        $error = '';

        if (Tools::isSubmit('submitUpdateGroups')) {
            $groups = $this->getGroupsParrain();
            foreach ($groups as $group) {
                if (!Db::getInstance()->update('group_lang',
                    array('parrain' => Tools::getValue($group['id_group'])),
                    'id_group = ' . $group['id_group'])
                ) {
                    $error .= $this->l('Erreur lors de la mise à jour des groupes');
                }
            }
        } elseif (Tools::isSubmit('submitUpdateCodeAction')) {
            $codes_action = $this->getAllCodesAction();
            foreach ($codes_action as $code) {
                if (!Db::getInstance()->update('code_action',
                    array('groupe' => Tools::getValue($code['id_code_action'])),
                    'id_code_action = ' . $code['id_code_action'])
                ) {
                    $error .= $this->l('Erreur lors de la mise à jour des codes action.');
                }
            }
        }

        if ($error) {
            $this->html .= $this->displayError($error);
        } else {
//            $this->html .= $this->displayConfirmation($this->l('Groupement des codes action mis à jour.'));

        }
    }

    private function displayForm()
    {
        $this->html .= $this->generateFormCodeAction();
        $this->html .= $this->generateForm();
        $this->html .= $this->display(__FILE__, 'configuration.tpl');
    }

    private function generateFormCodeAction()
    {
        $codesAction = $this->getAllCodesAction();
        $inputs = array();
        foreach ($codesAction as $code => $value) {
            $inputs[] = array(
                'type' => 'select',
                'label' => $value['description'] . ' (' . $value['name'] . ')',
                'name' => $value['id_code_action'],
                'options' => array(
                    'query' => $codesAction,
                    'id' => 'id_code_action',
                    'name' => 'name'
                ),
            );
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Groupement des codes action.'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitUpdateCodeAction'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigCodeAction(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));

    }

    private function generateForm()
    {
        $groupsParrain = $this->getGroupsParrain();
        $inputs = array();
        foreach ($groupsParrain as $group => $value) {
            $inputs[] = array(
                'type' => 'switch',
                'label' => $value['name'],
                'name' => $value['id_group'],
                'desc' => $this->l('Groupe Parrain ?'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_ff',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                )
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Affecter un groupe en tant que parrain.'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitUpdateGroups'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigGroupsParrain(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    private function getConfigCodeAction()
    {
        $code_action = array();
        $codes = $this->getAllCodesAction();
        foreach ($codes as $code => $value) {
            $code_action[$value['id_code_action']] = $value['groupe'];
        }

        return $code_action;
    }

    /**
     * Retourne la configuration parrain de chaque group
     * @return array (id_group => value_parrain)
     */
    private function getConfigGroupsParrain()
    {
        $groups_parrain = array();
        $groups = $this->getGroupsParrain();
        foreach ($groups as $group => $value) {
            $groups_parrain[$value['id_group']] = $value['parrain'];
        }

        return $groups_parrain;
    }

    /**
     * Retourn tous les groupes avec leurs configuration
     * @return array (id_group, name, value_parrain)
     */
    public function getGroupsParrain()
    {
        $sql = 'SELECT id_group, name, parrain FROM `' . _DB_PREFIX_ . 'group_lang` WHERE id_lang = ' .
            intval(Configuration::get('PS_LANG_DEFAULT'));
        $groups = Db::getInstance()->executeS($sql);

        return $groups;
    }

    private function createCodeActionTable()
    {
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'code_action` (
        `id_code_action` INT(12) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(64) NOT NULL,
        `description` VARCHAR(255) NULL,
        `groupe` VARCHAR(64) NULL, 
        PRIMARY KEY (`id_code_action`))
          ENGINE =' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $this->table_charset . ';';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        $data = $this->dataCodeAction();
        if (!Db::getInstance()->insert('code_action', $data)) {
            return false;
        }

        return true;
    }

    private function removeCodeActionTable()
    {
        if (!Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'code_action`')) {
            return false;
        }

        return true;
    }

    private function dataCodeAction()
    {
        $code_action = array(
            'ABO' => array('Abonnement', '1'),
            'PROSP' => array('Prospection', '2'),
            'PROSP1' => array('Prospection tracer 1', '2'),
            'PROSP11' => array('Prospection tracer 11', '2'),
            'PROSP12' => array('Prospection tracer 12', '2'),
            'PROSP13' => array('Prospection tracer 13', '2'),
            'PROSP2' => array('Prospection tracer 2', '2'),
            'PROSP21' => array('Prospection tracer 21', '2'),
            'PROSP22' => array('Prospection tracer 22', '2'),
            'PROSP23' => array('Prospection tracer 23', '2'),
            'PROSP24' => array('Prospection tracer 24', '2'),
            'PROSP3' => array('Prospection tracer 3', '2'),
            'PROSP5' => array('Prospection tracer 5', '2'),
            'PROSP51' => array('Prospection tracer 51', '2'),
            'PROSP52' => array('Prospection tracer 52', '2'),
            'PROSP53' => array('Prospection tracer 53', '2'),
            'PROSP ENTR' => array('Contact entrant sans fiche client', '2'),
            'FID' => array('FID', '18'),
            'FID PROMO' => array('FID suite promo', '18'),
            'FID PROG F' => array('FID programme fidélité', '18'),
            'PAR' => array('Parrainage', '21'),
            'REACT+4M' => array('Reactivation fichier clients +4mois', '22'),
            'REACT+4MPROMO' => array('Reactivation fichier clients +4mois suite à promo', '22'),
            'REACT SPONT' => array('Reactivation client spontanée', '22'),
            'REACT SPONT PROMO' => array('Reactivation client spontanée suite à promo', '22'),
            'REACT AC FORM' => array('Reactivation client AC formulaire', '22')
        );
        $data = array();
        $c = 1;
        foreach ($code_action as $key => $value) {
            $data[] = array(
                'id_code_action' => $c,
                'name' => $key,
                'description' => $value[0],
                'groupe' => $value[1]
            );
            $c++;
        }

        return $data;
    }

    private function updateOrdersTable()
    {
        $this->updateOrdersTableIdEmployee();
        $this->updateOrdersTableIdCodeAction();

        return true;
    }

    private function updateOrdersTableIdEmployee()
    {
        $reqCoachs = new DbQuery();
        $reqCoachs->select('DISTINCT coach, e.id_employee')
            ->from('orders')
            ->leftJoin('employee', 'e', 'e.lastname = coach');

        $listCoachs = Db::getInstance()->executeS($reqCoachs);

        foreach ($listCoachs as $coach) {
            if (!empty($coach['id_employee'])) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET id_employee = ' . $coach['id_employee'] . '
            WHERE coach = "' . $coach['coach'] . '"';
                Db::getInstance()->execute($sql);
            }
        }
    }

    private function updateOrdersTableIdCodeAction()
    {
        $listCodesAction = $this->getAllCodesAction();

        foreach ($listCodesAction as $code) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET id_code_action = ' . $code['id_code_action'] . '
            WHERE  code_action = "' . $code['name'] . '"';
            Db::getInstance()->execute($sql);
        }
    }

    private function getAllCodesAction()
    {
        $sql = 'SELECT id_code_action, name, description, groupe FROM `' . _DB_PREFIX_ . 'code_action`';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    private function getCodeAction($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE id_code_action = '. (intval($id));

        return Db::getInstance()->getRow($sql);
    }

    private function getAllGroupeCodesAction()
    {
        $sql = 'SELECT DISTINCT groupe FROM `' . _DB_PREFIX_ . 'code_action`
        ';
        $groupes = Db::getInstance()->executeS($sql);

        $listGroupes = array();
        foreach ($groupes as $groupe) {
            $listGroupes[] = $this->getCodeAction($groupe['groupe']);
        }

        return $listGroupes;
    }

    public function hookAdminStatsModules($params)
    {
        $engine_params = array(
            'id' => 'id_order',
            'title' => $this->displayName,
            'columns' => $this->columns,
            'defaultSortColumn' => $this->default_sort_column,
            'defaultSortDirection' => $this->default_sort_direction,
            'emptyMessage' => $this->empty_message,
            'pagingMessage' => $this->paging_message
        );

        if (Tools::getValue('export')) {
            $this->csvExport($engine_params);
        }

        $this->smarty->assign(array(
            'displayName' => $this->displayName,
            'CSVExport' => $this->l('CSV Export'),
            'CSVLink' => Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1')
        ));

        $this->html .= $this->display(__FILE__, 'headerstats.tpl');
        $this->html .= $this->syntheseCoachs();
        $this->html .= $this->engine($engine_params);
        $this->html .= $this->display(__FILE__, 'footerstats.tpl');

        return $this->html;
    }

    public function hookActionValidateOrder($params)
    {
        $employee = (isset($this->context->employee->id)) ? $this->context->employee->id : false;
        if ($employee) {
            $idOrder = Order::getOrderByCartId($this->context->cart->id);
            $reqOrder = new DbQuery();
            $reqOrder->select('id_order, coach, code_action')
                ->from('orders')
                ->where('id_order = ' . $idOrder);
            $order = Db::getInstance()->getRow($reqOrder);

            if (!empty($order)) {
                $id_coach = $this->getIdCoach($order['coach']);
                $id_code_action = $this->getIdCodeAction($order['code_action']);

                if (!empty($id_coach) && !empty($id_code_action)) {
                    $req = 'UPDATE `' . _DB_PREFIX_ . 'orders`
                    SET id_employee = ' . $id_coach . ', id_code_action = ' . $id_code_action . ' 
                    WHERE id_order = ' . $order['id_order'];

                    Db::getInstance()->execute($req);
                }
            }
        }
    }

    protected function getData()
    {
        $this->query = '
          SELECT SQL_CALC_FOUND_ROWS SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) as total 
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1
				AND id_employee = 30
				AND date_add BETWEEN ' . $this->getDate();


        if (Validate::IsName($this->_sort)) {
            $this->query .= ' ORDER BY `' . bqSQL($this->_sort) . '`';
            if (isset($this->_direction) && (Tools::strtoupper($this->_direction) == 'ASC' || Tools::strtoupper($this->_direction) == 'DESC'))
                $this->query .= ' ' . pSQL($this->_direction);
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit))
            $this->query .= ' LIMIT ' . (int)$this->_start . ', ' . (int)$this->_limit;

        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
    }

    private function getIdCoach($coach)
    {
        $req = new DbQuery();
        $req->select('id_employee')
            ->from('employee')
            ->where('lastname = ' . $coach);

        return Db::getInstance()->getValue($req);
    }

    private function getIdCodeAction($code_action)
    {
        $req = new DbQuery();
        $req->select('id_code_action')
            ->from('code_action')
            ->where('name = ' . $code_action);

        return Db::getInstance()->getValue($req);
    }


    private function syntheseCoachs()
    {
        $html = $this->display(__FILE__, 'synthesecoachs/synthesecoachsheader.tpl');
        $html .= $this->syntheseCoachsFilter();

        $html .= $this->display(__FILE__, 'synthesecoachs/synthesecoachscontent.tpl');
        $html .= $this->display(__FILE__, 'synthesecoachs/synthesecoachsfooter.tpl');
        return $html;
    }


    private function syntheseCoachsFilter()
    {
        $linkFilterCoachs = AdminController::$currentIndex . '&module=' . $this->name
            . '&token=' . Tools::getValue('token');
        $this->smarty->assign(array(
            'linkFilter' => $linkFilterCoachs,
        ));
        $this->syntheseCoachsFilterCoach();
        $this->syntheseCoachsFilterCodeAction();
        return $this->display(__FILE__, 'synthesecoachs/synthesecoachsfilter.tpl');

    }

    private function syntheseCoachsFilterCoach()
    {
        $this->idFilterCoach = (int)$this->context->employee->id;
        $idProfil = $this->context->employee->id_profile;

        if ($this->viewAllCoachs[$idProfil]) {
            $listCoaches = Employee::getEmployees();
            $listCoaches[] = array(
                'id_employee' => '0',
                'lastname' => 'Tous les coachs',
                'firstname' => '---');

            if (Tools::isSubmit('submitFilterCoachs')) {
                $this->context->cookie->cdmoculeca_id_filter_coach = Tools::getValue('filterCoach');
            }
            $this->idFilterCoach = $this->context->cookie->cdmoculeca_id_filter_coach;

            $this->smarty->assign(array(
                'coachs' => $listCoaches,
                'filterActif' => (int)$this->context->cookie->cdmoculeca_id_filter_coach,
            ));
        }
    }

    private function syntheseCoachsFilterCodeAction()
    {
        if (Tools::isSubmit('submitFilterCodeAction')) {
            $this->context->cookie->cdmoduleca_id_filter_code_action = Tools::getValue('filterCodeAction');
        }
        $this->idFilterCodeAction = ($this->context->cookie->cdmoduleca_id_filter_code_action)
            ?$this->context->cookie->cdmoduleca_id_filter_code_action:'0';

        $listCodesAction = $this->getAllGroupeCodesAction();
        $listCodesAction[] = array(
            'id_code_action' => '0',
            'name' => 'Tous les codes'
        );
        $this->context->smarty->assign(array(
            'codesAction' => $listCodesAction,
            'filterCodeAction' => $this->idFilterCodeAction
        ));
    }

}
