<?php

class CaTools
{
    static public function getEmployees($active = 0, $id = null)
    {
        $sql = 'SELECT `id_employee`, `firstname`, `lastname`
			FROM `' . _DB_PREFIX_ . 'employee` ';
        $sql .= ($active == 'on') ? 'WHERE active = 1 ' : '';
        $sql .= ($id) ? ' WHERE id_employee = ' . $id : '';
        $sql .= ' ORDER BY `id_employee` ASC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $idCoach
     * @param $idFilterCodeAction
     * @param $dateBetween
     * @return mixed
     */
    static public function getCaCoachsTotal($idCoach = 0, $idFilterCodeAction, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . $idCoach : '';

        $filterCodeAction = '';
        if ($idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.id_code_action != ' . CaTools::getCodeActionByName('ABO');
        } elseif ($idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.id_code_action = ' . $idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) < 
                0 , 0, SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2))) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ';
        $sql .= $dateBetween;
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    static public function getCodeActionByName($name)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE name = "' . pSQL($name) . '"';

        return Db::getInstance()->getValue($sql);
    }

}
