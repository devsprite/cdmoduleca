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

    public static function getAllProspectsGroup($id_group, $limit = 50, $date_max = 12, $rand = false)
    {
        if ($rand) {
            $order = ' ORDER BY RAND() ';
        }else{
            $order = ' ORDER BY cu.`date_add` DESC ';
        }
        $sql = 'SELECT cu.`id_customer`, CONCAT(UPPER(cu.`lastname`)," ", LOWER(cu.`firstname`)) AS nom, cu.`date_add`,
          (SELECT GROUP_CONCAT(`id_group` SEPARATOR ", ") FROM `ps_customer_group` AS pcg
           WHERE pcg.`id_customer` = cu.`id_customer` GROUP BY cu.`id_customer`) AS id_group
          FROM `ps_customer` AS cu
          LEFT JOIN `ps_customer_group` AS cg ON cu.`id_customer` = cg.`id_customer`
          LEFT JOIN `ps_prospect` AS p ON cg.`id_customer` = p.`id_customer`
          WHERE cg.`id_group` = "' . (int)$id_group . '"
          AND cu.`date_add` > "'.date('Y-m-d', strtotime('-'.$date_max.' month')).'"
          AND cu.`deleted` = 0';
        $sql .=  $order;


        $sql .= ' LIMIT ' . $limit;

        $req = Db::getInstance()->executeS($sql);

        return $req;
    }
}