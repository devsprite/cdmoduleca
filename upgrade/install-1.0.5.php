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
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2016 Chez-Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit();
}

function upgrade_module_1_0_5($object, $install = false)
{
    if (!addColumnHistostatsmain_1_0_5($object) ||
        !addColumnHistoobjectifcoach_1_0_5($object)
    ) {
        return false;
    }

    return true;
}

function addColumnHistoobjectifcoach_1_0_5($object)
{
    $sql = 'ALTER TABLE `'._DB_PREFIX_.'histoobjectifcoach`
            ADD COLUMN `projectif` DECIMAL(16,2) NULL AFTER `caCoach`';

    if (!DB::getInstance()->execute($sql)){
        return false;
    }

    return true;
}

function addColumnHistostatsmain_1_0_5($object)
{
    $sql = 'ALTER TABLE `'._DB_PREFIX_.'histostatstable`
            ADD COLUMN `panierMoyenFid` DECIMAL(16,2) NULL AFTER `panierMoyen`';

    if (!DB::getInstance()->execute($sql)){
        return false;
    }

    return true;
}