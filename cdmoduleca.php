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
    protected $errors = array();
    protected $html = '';
    protected $query;
    protected $columns;
    protected $default_sort_column;
    protected $default_sort_direction;
    protected $empty_message;
    protected $paging_message;
    protected $config = array(
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
        $this->empty_message = $this->l('Pas d\'enregistrement disponible');
        $this->paging_message = sprintf($this->l('Affichage %1$s de %2$s'), '{0} - {1}', '{2}');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->columns = array(
            'id' => 'code',
            'header' => $this->l('Code'),
            'dataIndex' => 'code',
            'align' => 'left'
        );
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->alterGroupLangTable() ||
            !$this->installConfig() ||
            !$this->registerHook('AdminStatsModules')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->alterGroupLangTable('remove') ||
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

    public function hookAdminStatsModules($params)
    {
        $engine_params = array(
            'id' => 'id_product',
            'title' => $this->displayName,
            'columns' => $this->columns,
            'defaultSortColumn' => $this->default_sort_column,
            'defaultSortDirection' => $this->default_sort_direction,
            'emptyMessage' => $this->empty_message,
            'pagingMessage' => $this->paging_message
        );

        if (Tools::getValue('export'))
            $this->csvExport($engine_params);

        $this->html = '
			<div class="panel-heading">
				' . $this->displayName . '
			</div>
			' . $this->engine($engine_params) . '
			<a class="btn btn-default export-csv" href="' . Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1') . '">
				<i class="icon-cloud-upload"></i> ' . $this->l('CSV Export') . '
			</a>';

        return $this->html;
    }

    protected function getData()
    {
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $this->query = 'SELECT SQL_CALC_FOUND_ROWS cr.code, ocr.name, COUNT(ocr.id_cart_rule) as total, ROUND(SUM(o.total_paid_real) / o.conversion_rate,2) as ca
				FROM ' . _DB_PREFIX_ . 'order_cart_rule ocr
				LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = ocr.id_order
				LEFT JOIN ' . _DB_PREFIX_ . 'cart_rule cr ON cr.id_cart_rule = ocr.id_cart_rule
				WHERE o.valid = 1
					' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
					AND o.invoice_date BETWEEN ' . $this->getDate() . '
				GROUP BY ocr.id_cart_rule';
        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
        foreach ($values as &$value)
            $value['ca'] = Tools::displayPrice($value['ca'], $currency);
        $this->_values = $values;
        $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

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

            if ($error)
                $this->html .= $this->displayError($error);
            else
                $this->html .= $this->displayConfirmation($this->l('Settings Updated'));
        }
    }

    private function displayForm()
    {
        $this->html .= $this->generateForm();
        $this->html .= $this->display(__FILE__, 'configuration.tpl');
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
                    'title' => $this->l('Settings'),
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
        $helper->submit_action = 'submitUpdate';
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
        $groups = array();
        $sql = 'SELECT id_group, name, parrain FROM `' . _DB_PREFIX_ . 'group_lang` WHERE id_lang = ' .
            intval(Configuration::get('PS_LANG_DEFAULT'));
        $groups = Db::getInstance()->executeS($sql);

        return $groups;
    }

}
