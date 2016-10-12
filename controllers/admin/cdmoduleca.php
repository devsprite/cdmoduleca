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

class StatsCdModuleCa extends ModuleGrid
{
    public static function hookAdminStatsModules($object)
    {
        $engine_params = array(
            'id' => 'id_order',
            'title' => $object->displayName,
            'columns' => $object->columns,
            'defaultSortColumn' => $object->default_sort_column,
            'defaultSortDirection' => $object->default_sort_direction,
            'emptyMessage' => $object->empty_message,
            'pagingMessage' => $object->paging_message
        );

        if (Tools::getValue('export'))
            $object->csvExport($engine_params);
        $object->smarty->assign(array(
            'displayName' => $object->displayName,
            'CSVExport' => $object->l('CSV Export'),
            'CSVLink' => Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1')
        ));

        $object->html .= $object->display(__FILE__, 'headerstats.tpl');
        $object->html .= $object->engine($engine_params);
        $object->html .= $object->display(__FILE__, 'footerstats.tpl');

        return $object->html;
    }

    public static function getDataCdModuleCa($object)
    {
        $object->query = '
          SELECT SQL_CALC_FOUND_ROWS ROUND(SUM(o.total_products - o.total_discounts_tax_excl),2) as total 
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1
				AND id_employee = 30
				AND date_add BETWEEN ' . $object->getDate();


        if (Validate::IsName($object->_sort)) {
            $object->query .= ' ORDER BY `' . bqSQL($object->_sort) . '`';
            if (isset($object->_direction) && (Tools::strtoupper($object->_direction) == 'ASC' || Tools::strtoupper($object->_direction) == 'DESC'))
                $object->query .= ' ' . pSQL($object->_direction);
        }

        if (($object->_start === 0 || Validate::IsUnsignedInt($object->_start)) && Validate::IsUnsignedInt($object->_limit))
            $object->query .= ' LIMIT ' . (int)$object->_start . ', ' . (int)$object->_limit;

        $values = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($object->query);

        return $values;
    }

    protected function getData()
    {
        // TODO: Implement getData() method.
    }
}