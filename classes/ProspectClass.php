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
            'contacte' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 64),
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
        $sql = 'SELECT * FROM `ps_prospect` WHERE `id_prospect_attribue` = ' . $id_prospect_attribue;
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public static function getLastCustomer()
    {
        $sql = 'SELECT MAX(`id_customer`) FROM `ps_prospect`';
        $req = DB::getInstance()->getValue($sql);

        return (int)$req;
    }


}