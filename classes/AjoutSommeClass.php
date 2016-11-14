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

class AjoutSomme extends ObjectModel
{
    public $id_ajout_somme;
    public $id_employee;
    public $id_order;
    public $somme;
    public $commentaire;
    public $date_ajout_somme;
    public $date_add;

    public static $definition = array(
        'table' => 'ajout_somme',
        'primary' => 'id_ajout_somme',
        'fields' => array(
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
            'somme' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'commentaire' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_ajout_somme' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );
}
