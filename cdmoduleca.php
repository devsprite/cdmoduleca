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
            !$this->eraseConfig()
        ) {
            return false;
        }

        return true;
    }

    private function installConfig()
    {
        $this->initialyseConfigurationGroupsParrain();

        foreach ($this->config as $keyname => $value) {
            Configuration::updateValue($keyname, $value);
        }

        return true;
    }

    private function eraseConfig()
    {
        $this->eraseConfigurationGroupsParrain();

        foreach ($this->config as $keyname => $value) {
            Configuration::deleteByName($keyname);
        }

        return true;
    }

    public function getContent()
    {
        $groups_parrain = $this->getGroupsParrain();

//        ddd($groups_parrain);

        return 1;
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

    private function getGroupsParrain()
    {
        $lang = intval(Configuration::get('PS_LANG_DEFAULT'));
        $sql = 'SELECT id_group FROM `' . _DB_PREFIX_ . 'group_lang` WHERE id_lang = ' . $lang;
        $groups = Db::getInstance()->executeS($sql);

        return $groups;
    }

    private function initialyseConfigurationGroupsParrain()
    {
        $groups = $this->getGroupsParrain();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                Configuration::updateValue('CDMODULECA_GROUP_' . $group['id_group'], $group['id_group'] . ',' . '0');
            }
        }
    }

    private function eraseConfigurationGroupsParrain()
    {
        $groups_parrain = $this->getGroupsParrain();
        foreach ($groups_parrain as $keyName => $value) {
            Configuration::deleteByName('CDMODULECA_GROUP_' . $value['id_group']);
        }
    }
}
