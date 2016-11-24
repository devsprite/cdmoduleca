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

/**
 * Class AppelClass
 * Historique des stats
 */
class HistoStatsMainClass extends ObjectModel
{
    public $id_histostatsmain;
    public $datepickerFrom;
    public $datepickerTo;
    public $filterCoach;
    public $caAjuste;
    public $caTotal;
    public $caFidTotal;
    public $CaProsp;
    public $caDeduit;
    public $primeCA;
    public $primeVenteGrAbo;
    public $primeFichierCoach;
    public $primeParrainage;
    public $ajustement;
    public $nbrJourOuvre;

    public static $definition = array(
        'table' => 'histostatsmain',
        'primary' => 'id_histostatsmain',
        'fields' => array(
            'datepickerFrom' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'datepickerTo' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'filterCoach' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'caAjuste' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'caTotal' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'caFidTotal' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'CaProsp' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'caDeduit' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'primeCA' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'primeVenteGrAbo' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'primeFichierCoach' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'primeParrainage' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'ajustement' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'nbrJourOuvre' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
        ),
    );
}