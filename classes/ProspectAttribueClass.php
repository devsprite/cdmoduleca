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
}