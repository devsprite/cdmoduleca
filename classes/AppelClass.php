<?php

class AppelClass extends ObjectModel
{
    public $id_appel;
    public $id_employee;
    public $compteur;
    public $date_upd;

    public static $definition = array(
        'table' => 'appel',
        'primary' => 'id_appel',
        'fields' => array(
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
            'compteur' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function getCompteur($id_employee, $date_jour)
    {
        $sql = 'SELECT `compteur`, `id_appel` FROM `ps_appel`
                WHERE `id_employee` = '.(int)$id_employee.' 
                AND  date(`date_upd`) = "' . pSQl($date_jour) .'"';

        $req = Db::getInstance()->getRow($sql);

        return $req;
    }
}