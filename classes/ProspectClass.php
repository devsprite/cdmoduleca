<?php

class ProspectClass extends ObjectModel
{
    public $id_prospect;
    public $id_customer;
    public $id_prospect_attribue;
    public $traite;
    public $injoignable;
    public $contacte;
    public $date_add;

    public static $definition = array(
        'table' => 'prospect',
        'primary' => 'id_prospect',
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'id_prospect_attribue' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'traite' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
            'injoignable' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
            'contacte' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false, 'size' => 64),
        ));

    public static function getAllProspectsGroup($id_group, $limit = 50, $index_id = 0)
    {
        $order = ($index_id == 0) ? 'DESC' : 'ASC';
        $sql = 'SELECT cu.`id_customer`, CONCAT(UPPER(cu.`lastname`)," ", LOWER(cu.`firstname`)) AS nom, cu.`date_add`,
          (SELECT GROUP_CONCAT(`id_group` SEPARATOR ", ") FROM `ps_customer_group` AS pcg
           WHERE pcg.`id_customer` = cu.`id_customer` GROUP BY cu.`id_customer`) AS id_group
          FROM `ps_customer` AS cu
          LEFT JOIN `ps_customer_group` AS cg ON cu.`id_customer` = cg.`id_customer`
          LEFT JOIN `ps_prospect` AS p ON cg.`id_customer` = p.`id_customer`
          WHERE cg.`id_group` = "' . (int)$id_group . '"
          AND cu.id_customer > ' . $index_id . '
          AND cu.`deleted` = 0';
        $sql .= ' ORDER BY cu.`id_customer` ' . $order;

        $sql .= ' LIMIT ' . $limit;

        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public static function getProspectsByIdPa($id_prospect_attribue)
    {
        $sql = 'SELECT p.* , pa.*, cu.firstname, cu.lastname, e.lastname as coach FROM `ps_prospect` AS p 
                LEFT JOIN `ps_prospect_attribue` AS pa ON p.`id_prospect_attribue` = pa.`id_prospect_attribue`
                LEFT JOIN `ps_customer` AS cu ON p.`id_customer` = cu.`id_customer`
                LEFT JOIN `ps_employee` AS e ON pa.`id_employee` = e.`id_employee`
                WHERE p.`id_prospect_attribue` = ' . $id_prospect_attribue;
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public static function getLastCustomer()
    {
        $sql = 'SELECT MAX(`id_customer`) FROM `ps_prospect`';
        $req = DB::getInstance()->getValue($sql);

        return (int)$req;
    }

    public static function toggleTraite()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }

        if (Db::getInstance()->getValue('SELECT `traite` FROM `ps_prospect` WHERE `id_customer` = ' . $id) == 'Oui') {
            if (!Db::getInstance()->update('prospect', array('traite' => 'Non'), 'id_customer = ' . $id)) ;
            {
                return false;
            }
        } else {
            if (!Db::getInstance()->update('prospect', array('traite' => 'Oui'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        }
        return true;
    }

    public static function toggleInjoignable()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }
        if (Db::getInstance()->getValue('SELECT `injoignable` FROM `ps_prospect` WHERE `id_customer` = ' . $id) == 'Oui') {
            if (!Db::getInstance()->update('prospect', array('injoignable' => 'Non'), 'id_customer = ' . $id)) ;
            {
                return false;
            }
        } else {
            if (!Db::getInstance()->update('prospect', array('injoignable' => 'Oui'), 'id_customer = ' . (int)$id)) {
                return false;
            }
        }
        return true;
    }

    public static function setContact()
    {
        $id = (int)Tools::getValue('id_customer');

        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }

        $midi_debut = date('H:i:s', strtotime('12:00:00'));
        $apresmidi_debut = date('H:i:s', strtotime('14:00:00'));
        $soir_debut = date('H:i:s', strtotime('18:00:00'));
        $now = date('H:i:s');
        $prospect = ProspectClass::getProspectsByIdCu($id);

        $contacte = Tools::jsonDecode($prospect->contacte);

        switch ($now) {
            case ($now < $midi_debut) :
                $contacte->matin[] = date('d-m-Y H:i:s');
                break;
            case ($now >= $midi_debut && $now < $apresmidi_debut) :
                $contacte->midi[] = date('d-m-Y H:i:s');
                break;
            case ($now >= $apresmidi_debut && $now < $soir_debut):
                $contacte->apres_midi[] = date('d-m-Y H:i:s');
                break;
            case ($now >= $soir_debut) :
                $contacte->soir[] = date('d-m-Y H:i:s');
                break;
            default :
        }

        $prospect->contacte = Tools::jsonEncode($contacte);
        $prospect->update();
    }

    public static function setRepondeur()
    {
        $id = (int)Tools::getValue('id_customer');
        if (!ProspectClass::isProspectExistByIdCustomer($id)) {
            return false;
        }
        $prospect = ProspectClass::getProspectsByIdCu($id);
        $contacte = Tools::jsonDecode($prospect->contacte);
        $contacte->repondeur[] = date('d-m-Y H:i:s');
        $prospect->contacte = Tools::jsonEncode($contacte);
        $prospect->update();
    }

    private static function getProspectsByIdCu($id)
    {
        $sql = 'SELECT `id_prospect` FROM `ps_prospect` WHERE `id_customer` = ' . (int)$id;
        $req = Db::getInstance()->getValue($sql);
        $p = new ProspectClass($req);

        return $p;
    }

    public static function isProspectExistByIdCustomer($id)
    {
        $sql = 'SELECT `id_prospect` FROM `ps_prospect` WHERE `id_customer` = ' . (int)$id;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

}