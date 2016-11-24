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

function upgrade_module_1_0_1($object, $install = false)
{
    if (!createTableHistostatsmain($object) or
        !createTableHistostatstable($object) or
        !createTableHistoajoutsomme($object) or
        !createTableHistoobjectifcoach($object) or
        !$object->createTabsHistorique()
    ) {
        return false;
    }

    return true;
}

function createTableHistostatsmain($object)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'histostatsmain` (
            `id_histostatsmain` INT(12) NOT NULL AUTO_INCREMENT,
            `datepickerFrom` TEXT NOT NULL,
            `datepickerTo` TEXT NOT NULL,
            `filterCoach` TEXT NOT NULL,
            `caAjuste` DECIMAL(16,2) NULL,
            `caTotal` DECIMAL(16,2) NULL,
            `caFidTotal` DECIMAL(16,2) NULL,
            `CaProsp` DECIMAL(16,2) NULL,
            `caDeduit` DECIMAL(16,2) NULL,
            `primeCA` DECIMAL(16,2) NULL,
            `primeVenteGrAbo` DECIMAL(16,2) NULL,
            `primeFichierCoach` DECIMAL(16,2) NULL,
            `primeParrainage` DECIMAL(16,2) NULL,
            `ajustement` DECIMAL(16,2) NULL,
            `nbrJourOuvre` INT(5) NULL,        
            PRIMARY KEY (`id_histostatsmain`))
            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $object->table_charset . ';';

    if (!DB::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}

function createTableHistostatstable($object)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'histostatstable` (
            `id_histostatstable` INT(12) NOT NULL AUTO_INCREMENT,
            `id_histostatsmain` INT(12) NOT NULL,
            `lastname` VARCHAR(128) NULL,
            `firstname` VARCHAR(128) NULL,
            `caAjuste` DECIMAL(16,2) NULL,
            `CaContact` DECIMAL(16,2) NULL,
            `tauxTransfo` DECIMAL(5,2) NULL,
            `NbreDeProspects` INT(7) NULL,
            `NbreVentesTotal` INT(7) NULL,
            `CaProsp` DECIMAL(16,2) NULL,
            `PourcCaProspect` DECIMAL(5,2) NULL,
            `caDejaInscrit` DECIMAL(16,2) NULL,
            `PourcCaFID` DECIMAL(5,2) NULL,
            `panierMoyen` DECIMAL(16,2) NULL,
            `caAvoir` DECIMAL(16,2) NULL,
            `pourCaAvoir` DECIMAL(5,2) NULL,
            `caImpaye` DECIMAL(16,2) NULL,
            `pourCaImpaye` DECIMAL(5,2) NULL,
            `totalVenteGrAbo` DECIMAL(16,2) NULL,
            `nbrVenteGrAbo` INT(7) NULL,
            `nbrVenteGrDesaAbo` INT(7) NULL,
            `pourcenDesabo` DECIMAL(5,2) NULL,
            `totalVenteGrPar` DECIMAL(16,2) NULL,
            `nbrVenteGrPar` INT(7) NULL,
            `pourVenteGrPar` DECIMAL(5,2) NULL,
            PRIMARY KEY (`id_histostatstable`))
            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $object->table_charset . ';';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}

function createTableHistoajoutsomme($object)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'histoajoutsomme` (
            `id_histoajoutsomme` INT(12) NOT NULL AUTO_INCREMENT,
            `id_histostatsmain` INT(12) NOT NULL,
            `lastname` VARCHAR(128) NOT NULL,
            `date_ajout_somme` VARCHAR(128) NOT NULL,
            `somme` DECIMAL(16,2) NULL,
            `commentaire` TEXT NOT NULL,
            `id_order` INT(12) NULL,
            PRIMARY KEY (`id_histoajoutsomme`))
            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $object->table_charset . ';';

    if (!DB::getInstance()->execute($sql)) {
        return false;
    }
    return true;
}

function createTableHistoobjectifcoach($object)
{
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'histoobjectifcoach` (
            `id_histoobjectifcoach` INT(12) NOT NULL AUTO_INCREMENT,
            `id_histostatsmain` INT(12) NOT NULL,
            `lastname` VARCHAR(128) NOT NULL,
            `class` VARCHAR(128) NULL,
            `date_start` VARCHAR(128) NOT NULL,
            `date_end` VARCHAR(128) NOT NULL,
            `somme` DECIMAL(16,2) NULL,
            `caCoach` DECIMAL(16,2) NULL,
            `pourcentDeObjectif` DECIMAL(5,2) NULL,
            `heure_absence` INT(8) NULL,
            `jour_absence` INT(8) NULL,
            `jour_ouvre` INT(8) NULL,
            `commentaire` TEXT NOT NULL,
            PRIMARY KEY (`id_histoobjectifcoach`))
            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $object->table_charset . ';';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }
    return true;
}
