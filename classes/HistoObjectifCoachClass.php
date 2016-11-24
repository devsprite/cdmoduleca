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
 * Class HistoAjoutSommeClass
 */
class HistoObjectifCoachClass extends ObjectModel
{
    public $id_histoobjectifcoach;
    public $id_histostatsmain;
    public $lastname;
    public $date_start;
    public $date_end;
    public $somme;
    public $caCoach;
    public $pourcentDeObjectif;
    public $heure_absence;
    public $jour_absence;
    public $jour_ouvre;
    public $commentaire;

    public static $definition = array(
        'table' => 'histoobjectifcoach',
        'primary' => 'id_histoobjectifcoach',
        'fields' => array(
            'id_histostatsmain' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_start' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_end' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'somme' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'caCoach' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'pourcentDeObjectif' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'heure_absence' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'jour_absence' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'jour_ouvre' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'commentaire' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        )
    );
}