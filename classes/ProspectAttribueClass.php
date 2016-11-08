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

class ProspectAttribueClass extends ObjectModel
{
    public $id_prospect_attribue;
    public $id_employee;
    public $nbr_prospect_attribue;
    public $date_debut;
    public $date_fin;

    public static $definition = array(
        'table' => 'prospect_attribue',
        'primary' => 'id_prospect_attribue',
        'fields' => array(
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'nbr_prospect_attribue' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId',
                'required' => false),
            'date_debut' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_fin' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
        )
    );

    public static function getListProspects($getDateBetween, $isAllow, $id_employee)
    {
        $filter = '';
        if (!$isAllow) {
            $filter = ' AND pa.`id_employee` = ' . (int)$id_employee;
        }

        $sql = 'SELECT pa.*, e.lastname, e.firstname FROM `' . _DB_PREFIX_ . 'prospect_attribue` AS pa
                LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON pa.id_employee = e.id_employee
                WHERE `date_debut` BETWEEN ' . $getDateBetween;
        $sql .= $filter;
        $sql .= ' ORDER BY e.`id_employee` ASC, `date_debut` DESC';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public static function isExist($id)
    {
        $sql = 'SELECT `id_prospect_attribue` FROM `' . _DB_PREFIX_ . 'prospect_attribue`
                WHERE `id_prospect_attribue` = ' . pSQL($id);
        $req = Db::getInstance()->getRow($sql);

        return ($req) ? true : false;
    }

    public static function getNbrProspectsAttriByCoach($id_employee = 0, $getDateBetween)
    {
        $filter = '';
        if ($id_employee != 0) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee . ' ';
        }
        $sql = 'SELECT SUM(`nbr_prospect_attribue`) FROM `' . _DB_PREFIX_ . 'prospect_attribue`
                WHERE `date_debut` BETWEEN ' . $getDateBetween . '
                AND `date_fin` BETWEEN ' . $getDateBetween;
        $sql .= $filter;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }
}
