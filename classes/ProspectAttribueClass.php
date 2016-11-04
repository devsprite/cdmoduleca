<?php

class ProspectAttribueClass extends ObjectModel
{
    public $id_prospect_attribue;
    public $id_employee;
    public $nbr_prospect_attribue;
    public $date_debut;
    public $date_fin;

    public static $definition = array(
        'table' => 'prospect_attribue',
        'primary' => 'id_prospect_attribue',
        'fields' => array(
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => true),
            'nbr_prospect_attribue' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => false),
            'date_debut' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_fin' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => true),
        )
    );

    public static function getListProspects($getDateBetween, $isAllow, $id_employee)
    {
        $filter = '';
        if (!$isAllow) {
            $filter = ' AND pa.`id_employee` = ' . (int)$id_employee;
        }

        $sql = 'SELECT pa.*, e.lastname, e.firstname FROM `ps_prospect_attribue` AS pa
                LEFT JOIN `ps_employee` AS e ON pa.id_employee = e.id_employee
                WHERE `date_debut` BETWEEN ' . $getDateBetween;
        $sql .= $filter;
        $sql .= ' ORDER BY e.`id_employee` ASC, `date_debut` DESC';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public static function isExist($id)
    {
        $sql = 'SELECT `id_prospect_attribue` FROM `ps_prospect_attribue`
                WHERE `id_prospect_attribue` = ' . pSQL($id);
        $req = Db::getInstance()->getRow($sql);

        return ($req) ? true : false;
    }

    public static function getNbrProspectsAttriByCoach($id_employee = 0, $getDateBetween)
    {
        $filter ='';
        if ($id_employee != 0 ) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee .' ';
        }
        $sql = 'SELECT SUM(`nbr_prospect_attribue`) FROM `ps_prospect_attribue`
                WHERE `date_debut` BETWEEN ' . $getDateBetween . '
                AND `date_fin` BETWEEN ' .$getDateBetween ;
        $sql .= $filter;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

}