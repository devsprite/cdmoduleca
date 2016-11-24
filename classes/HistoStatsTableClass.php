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
class HistoStatsTableClass extends ObjectModel
{
    public $id_histostatstable;
    public $id_histostatsmain;
    public $lastname;
    public $caAjuste;
    public $CaContact;
    public $tauxTransfo;
    public $NbreDeProspects;
    public $NbreVentesTotal;
    public $CaProsp;
    public $PourcCaProspect;
    public $caDejaInscrit;
    public $PourcCaFID;
    public $panierMoyen;
    public $caAvoir;
    public $pourCaAvoir;
    public $caImpaye;
    public $pourCaImpaye;
    public $totalVenteGrAbo;
    public $nbrVenteGrAbo;
    public $nbrVenteGrDesaAbo;
    public $pourcenDesabo;
    public $totalVenteGrPar;
    public $nbrVenteGrPar;
    public $pourVenteGrPar;

    public static $definition = array(
        'table' => 'histostatstable',
        'primary' => 'id_histostatstable',
        'fields' => array(
            'id_histostatsmain' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'caAjuste' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'CaContact' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tauxTransfo' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'NbreDeProspects' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'NbreVentesTotal' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'CaProsp' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'PourcCaProspect' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'caDejaInscrit' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'PourcCaFID' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'panierMoyen' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'caAvoir' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pourCaAvoir' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'caImpaye' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pourCaImpaye' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'totalVenteGrAbo' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'nbrVenteGrAbo' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'nbrVenteGrDesaAbo' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'pourcenDesabo' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'totalVenteGrPar' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'nbrVenteGrPar' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'pourVenteGrPar' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),

        )
    );

}