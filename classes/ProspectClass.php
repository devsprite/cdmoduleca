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
 * Class ProspectClass
 * Détails des prospects attribué à un coach et de l'état des contacts passé par le coach
 */
class ProspectClass extends ObjectModel
{
    public $id_prospect;
    public $id_customer;
    public $id_prospect_attribue;
    public $traite;
    public $injoignable;
    public $contacte;
    public $date_debut;
    public $date_add;
    public $message = array(
        'matin' => array(),
        'midi' => array(),
        'apres_midi' => array(),
        'soir' => array(),
        'repondeur' => array()
    );

    public static $definition = array(
        'table' => 'prospect',
        'primary' => 'id_prospect',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT,
                'validate' => 'isNullOrUnsignedId', 'required' => true),
            'id_prospect_attribue' => array('type' => self::TYPE_INT,
                'validate' => 'isNullOrUnsignedId', 'required' => true),
            'traite' => array('type' => self::TYPE_STRING,
                'validate' => 'isString', 'required' => false, 'size' => 64),
            'injoignable' => array('type' => self::TYPE_STRING,
                'validate' => 'isString', 'required' => false, 'size' => 64),
            'contacte' => array('type' => self::TYPE_STRING,
                'validate' => 'isString', 'required' => false),
            'date_debut' => array('type' => self::TYPE_DATE,
                'validate' => 'isDateFormat', 'required' => false, 'size' => 64),
            'date_add' => array('type' => self::TYPE_DATE,
                'validate' => 'isDateFormat', 'required' => false, 'size' => 64),
        ));

    /**
     * Sélection aléatoire des ancien ou nouveau prospects avant ou aprés la date d'index
     * @param $id_group
     * @param int $limit
     * @param $newProspects
     * @return array
     */
    public static function getAllProspectsGroup($id_group, $limit = 50, $newProspects )
    {
        // Nombre de jour max pour la selection des prospects
        $indexDate = Configuration::get('CDMODULECA_PROSPECTS_INDEX_DATE');
        $nbreJourMax = date('Y-m-d', strtotime($indexDate . ' -' . (int)Configuration::get('CDMODULECA_NBR_JOUR_MAX_PROSPECTS') . ' day'));
        $filter = '';
        if ($newProspects) {
            $filter = ' AND cu.`date_add` > "' . pSQL($indexDate) . '"';
        } else {
            $filter = ' AND cu.`date_add` > "' . pSQL($nbreJourMax) . '"
                        AND cu.`date_add` < "' . pSQL($indexDate) . '"';
        }

        $sql = 'SELECT cu.`id_customer`, CONCAT(UPPER(cu.`lastname`)," ", LOWER(cu.`firstname`)) AS nom, cu.`date_add`,
          (SELECT GROUP_CONCAT(`id_group` SEPARATOR ", ") FROM `' . _DB_PREFIX_ . 'customer_group` AS pcg
           WHERE pcg.`id_customer` = cu.`id_customer` GROUP BY cu.`id_customer`) AS id_group
          FROM `' . _DB_PREFIX_ . 'customer` AS cu
          LEFT JOIN `' . _DB_PREFIX_ . 'customer_group` AS cg ON cu.`id_customer` = cg.`id_customer`
          LEFT JOIN `' . _DB_PREFIX_ . 'prospect` AS p ON cg.`id_customer` = p.`id_customer`
          WHERE cg.`id_group` = "' . (int)$id_group . '" ';
        $sql .= $filter;
        $sql .= 'AND cu.`deleted` = 0';
        $sql .= ' ORDER BY RAND()';
        $sql .= ' LIMIT ' . (int)$limit;

        $req = Db::getInstance()->executeS($sql);
        return $req;
    }

    /**
     * Retourne les prospects en fonction de l'id prospect attribué
     * @param $id_prospect_attribue
     * @return array
     */
    public static function getProspectsByIdPa($id_prospect_attribue)
    {
        $sql = 'SELECT p.* , pa.*, cu.`firstname`, cu.`lastname`, e.`lastname` as coach 
                FROM `' . _DB_PREFIX_ . 'prospect` AS p 
                LEFT JOIN `' . _DB_PREFIX_ . 'prospect_attribue` AS pa 
                ON p.`id_prospect_attribue` = pa.`id_prospect_attribue`
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` AS cu ON p.`id_customer` = cu.`id_customer`
                LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON pa.`id_employee` = e.`id_employee`
                WHERE p.`id_prospect_attribue` = ' . (int)$id_prospect_attribue;
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    /**
     * Retourn l'id du dernier prospects (inutilisé)
     * @return int
     */
    public static function getLastCustomer()
    {
        $sql = 'SELECT MAX(`id_customer`) FROM `' . _DB_PREFIX_ . 'prospect`';
        $req = DB::getInstance()->getValue($sql);

        return (int)$req;
    }

    /**
     * Prospect traite ou pas
     * @return bool
     */
    public static function toggleTraite()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }

        $v = Db::getInstance()->getValue('SELECT `traite` FROM `' . _DB_PREFIX_ . 'prospect` 
                WHERE `id_customer` = ' . $id);
        if ($v == 'non') {
            if (!Db::getInstance()->update('prospect', array('traite' => 'oui'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        } else {
            if (!Db::getInstance()->update('prospect', array('traite' => 'non'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prospect injoignable ou pas
     * @return bool
     */
    public static function toggleInjoignable()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }
        if (Db::getInstance()->getValue('SELECT `injoignable` FROM `' . _DB_PREFIX_ . 'prospect` 
        WHERE `id_customer` = ' . $id) == 'oui'
        ) {
            if (!Db::getInstance()->update('prospect', array('injoignable' => 'non'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        } else {
            if (!Db::getInstance()->update('prospect', array('injoignable' => 'oui'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Enregistre un contact prospect fait par le coach, met à jour injoignable si tous les contacts sont fait
     * @return bool
     */
    public static function setContact()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }
        $c = Context::getContext();
        $lastname = $c->employee->lastname;

        $midi_debut = date('H:i:s', strtotime('12:00:00'));
        $apresmidi_debut = date('H:i:s', strtotime('14:00:00'));
        $soir_debut = date('H:i:s', strtotime('17:00:00'));
        $now = date('H:i:s');
        $prospect = ProspectClass::getProspectsByIdCu($id);

        $contacte = Tools::jsonDecode($prospect->contacte);

        if (empty($contacte)) {
            $prospect->traite = 'non';
        }

        switch ($now) {
            case ($now < $midi_debut):
                $contacte->matin[] = date('\L\e d-m-Y \à H:i:s') . ' - ' . $lastname;
                break;
            case ($now >= $midi_debut && $now < $apresmidi_debut):
                $contacte->midi[] = date('\L\e d-m-Y \à H:i:s') . ' - ' . $lastname;
                break;
            case ($now >= $apresmidi_debut && $now < $soir_debut):
                $contacte->apres_midi[] = date('\L\e d-m-Y \à H:i:s') . ' - ' . $lastname;
                break;
            case ($now >= $soir_debut):
                $contacte->soir[] = date('\L\e d-m-Y \à H:i:s') . ' - ' . $lastname;
                break;
            default:
        }

        if (
            (isset($contacte->matin) && count($contacte->matin) > 1) &&
            (isset($contacte->midi) && count($contacte->midi) > 1) &&
            (isset($contacte->apres_midi) && count($contacte->apres_midi) > 1) &&
            (isset($contacte->soir) && count($contacte->soir) > 1) &&
            (isset($contacte->repondeur) && count($contacte->repondeur > 2))
        ) {
            $prospect->injoignable = 'oui';
        }

        $prospect->contacte = Tools::jsonEncode($contacte);

        $prospect->update();
    }

    /**
     * Met à jour le contact répondeur, met à jour injoignable si tous les contacts sont fait
     * @return bool
     */
    public static function setRepondeur()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }

        $c = Context::getContext();
        $lastname = $c->employee->lastname;

        $prospect = ProspectClass::getProspectsByIdCu($id);
        $contacte = Tools::jsonDecode($prospect->contacte);

        if (empty($contacte)) {
            $prospect->traite = 'non';
        }

        $contacte->repondeur[] = date('\L\e d-m-Y \à H:i:s') . ' - ' . $lastname;
        $prospect->contacte = Tools::jsonEncode($contacte);

        if (
            (isset($contacte->matin) && count($contacte->matin) > 1) &&
            (isset($contacte->midi) && count($contacte->midi) > 1) &&
            (isset($contacte->apres_midi) && count($contacte->apres_midi) > 1) &&
            (isset($contacte->soir) && count($contacte->soir) > 1) &&
            (isset($contacte->repondeur) && count($contacte->repondeur > 2))
        ) {
            $prospect->injoignable = 'oui';
        }
        $prospect->update();
    }

    /**
     * Retourne l'objet prospect par son id_customer
     * @param $id
     * @return ProspectClass
     */
    private static function getProspectsByIdCu($id)
    {
        $sql = 'SELECT `id_prospect` FROM `' . _DB_PREFIX_ . 'prospect` WHERE `id_customer` = ' . (int)$id;
        $req = Db::getInstance()->getValue($sql);
        $p = new ProspectClass($req);

        return $p;
    }

    /**
     * Est-ce que le prospect existe par son id_customer
     * @param $id
     * @return mixed
     */
    public static function isProspectExistByIdCustomer($id)
    {
        $sql = 'SELECT `id_prospect` FROM `' . _DB_PREFIX_ . 'prospect` WHERE `id_customer` = ' . (int)$id;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * Retourne les prospects n'ayant pas de coach affecté
     * @return array
     */
    public static function getProspectsIsole()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'prospect` 
                WHERE `id_prospect_attribue`
                NOT IN 
                (SELECT `id_prospect_attribue` FROM `' . _DB_PREFIX_ . 'prospect_attribue` )
                AND `traite` != "Nouveau"
                AND `injoignable` = "non"';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }
}
