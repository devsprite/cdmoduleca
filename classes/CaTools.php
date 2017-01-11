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

require_once(dirname(__FILE__) . '/AppelClass.php');

class CaTools
{
    /**
     * Retourne les informations de l'employé ainsi que son groupe
     * @param null $active
     * @param null $id
     * @return array
     */
    public static function getEmployees($active = null, $id = null)
    {
        $sql = 'SELECT gl.`id_group`,e.`id_employee`, e.`firstname`, e.`lastname`
			FROM `' . _DB_PREFIX_ . 'employee` AS e ';
        $sql .= 'LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl ON e.`id_employee` = gl.`id_employee` ';
        $sql .= ($active == 'on') ? 'WHERE e.`active` = 1 ' : '';
        $sql .= ($id) ? ' WHERE e.`id_employee` = ' . (int)$id : '';
        $sql .= ' ORDER BY e.`lastname` ASC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Somme des commande valide mais remboursé
     * @param int $idCoach
     * @param $idFilterCodeAction
     * @param $dateBetween
     * @return mixed
     */
    public static function getCaCoachsRembourse($idCoach = 0, $idFilterCodeAction, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idCoach : '';

        $filterCodeAction = '';
        if ($idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.`id_code_action` != ' . pSQL(CaTools::getCodeActionByName('ABO'));
        } elseif ($idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.`id_code_action` = ' . (int)$idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2)) < 
                0 , 0, SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2))) as total
                FROM `' . _DB_PREFIX_ . 'orders` AS o';
        $sql .= ' WHERE `date_add` BETWEEN ';
        $sql .= $dateBetween;
        $sql .= ' AND `valid` = 1 ';
        $sql .= ' AND (`current_state` = 7)';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    public static function getCodeActionByName($name)
    {
        $sql = 'SELECT `groupe` FROM `' . _DB_PREFIX_ . 'code_action` WHERE `name` = "' . pSQL($name) . '"';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Chiffre d'affaire des client déjà inscrit
     * @param int $idFilterCoach
     * @param $dateBetween
     * @return int|string
     */
    public static function getCaDejaInscrit($idFilterCoach = 0, $dateBetween)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idFilterCoach : '';

        $sql = 'SELECT ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2) AS total,
                IF((SELECT so.`id_order` FROM `' . _DB_PREFIX_ . 'orders` so 
                WHERE so.`id_customer` = o.`id_customer` 
                AND so.`id_order` < o.`id_order` LIMIT 1) > 0, 1, 0) as notNew
				FROM `' . _DB_PREFIX_ . 'orders` AS o
				WHERE `valid` = 1
				AND o.`current_state` != 460
				AND o.`id_code_action` = 20';
        $sql .= $filterCoach;
        $sql .= ' AND `date_add` BETWEEN ' . $dateBetween;

        $caFID = Db::getInstance()->executeS($sql);

        $total = '';
        foreach ($caFID as $ca) {
            $total += ($ca['notNew'] == 1) ? $ca['total'] : 0;
        }

        return $total;
    }

    /**
     * Somme des commandes avec le code action à déduire dans le la configuration du module
     * @param int $idFilterCoach
     * @param $dateBetween
     * @return mixed
     */
    public static function getCaDeduit($idFilterCoach = 0, $dateBetween)
    {
        $listStatuts = explode(',', Configuration::get('CDMODULECA_ORDERS_STATE'));
        $sqlStatuts = ' AND ( ';
        foreach ($listStatuts as $statut) {
            $sqlStatuts .= ' `current_state` = ' . pSQL($statut) . ' OR ';
        }
        $sqlStatuts = Tools::substr($sqlStatuts, 0, -3) . ')';

        $filterCoach = ($idFilterCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idFilterCoach : '';

        $sql = 'SELECT SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2)) as total
                FROM `' . _DB_PREFIX_ . 'orders` AS o';
        $sql .= ' WHERE `date_add` BETWEEN ' . $dateBetween;
        $sql .= $filterCoach;
        $sql .= $sqlStatuts;

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Nombre de commande par coach, code_action, status
     * @param int $idCoach
     * @param int $idCodeAction
     * @param null $current_state
     * @param $dateBetween
     * @return mixed|string
     */
    public static function getNumberCommande($idCoach = 0, $idCodeAction = 0, $current_state = null, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idCoach : '';

        $filterCodeAction = ($idCodeAction != 0)
            ? ' AND `id_code_action` = ' . (int)$idCodeAction : '';
        $filter_current_state = '';
        if ($current_state) {
            $filter_current_state = ' AND ( ';
            foreach ($current_state as $value) {
                $filter_current_state .= " o.`current_state` != '" . (int)$value . "' AND ";
            }
            $filter_current_state = Tools::substr($filter_current_state, 0, -4) . ' )';
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_order
                FROM `' . _DB_PREFIX_ . 'orders` AS o
                WHERE `valid` = 1 ';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;
        $sql .= $filter_current_state;
        $sql .= ' AND `date_add` BETWEEN ' . $dateBetween;

        Db::getInstance()->executeS($sql);
        $nbr = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
        $nbr = ($nbr) ? $nbr : '';

        return $nbr;
    }

    /**
     * Retourne les informations du code action, name, groupement
     * @param $id
     * @return array
     */
    public static function getCodeAction($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE `id_code_action` = ' . ((int)$id);

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Retourne tous les groupements des codes action
     * @return array
     */
    public static function getAllGroupeCodesAction()
    {
        $sql = 'SELECT DISTINCT `groupe` FROM `' . _DB_PREFIX_ . 'code_action`
        ';
        $groupes = Db::getInstance()->executeS($sql);

        $listGroupes = array();
        foreach ($groupes as $groupe) {
            $listGroupes[] = CaTools::getCodeAction($groupe['groupe']);
        }

        return $listGroupes;
    }

    /**
     * Retourne la ligne correpondante à l'id de la table ajout_somme
     * @param $id
     * @return array
     */
    public static function getAjoutSommeById($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'ajout_somme` WHERE `id_ajout_somme` = ' . (int)$id;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Retourne les tuples en fonction de l'id employes et la l'intervalle de date
     * @param $id_employee
     * @param $dateBetween
     * @return array
     */
    public static function getAjoutSomme($id_employee, $dateBetween)
    {
        $sql = 'SELECT `id_ajout_somme`, `somme`, `id_order`, `commentaire`, a.`id_employee`, `date_ajout_somme`, `lastname`
                FROM `' . _DB_PREFIX_ . 'ajout_somme` AS a
                LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
                WHERE `date_ajout_somme` BETWEEN ' . $dateBetween;

        if ($id_employee != 0) {
            $sql .= ' AND a.`id_employee` = ' . (int)$id_employee;
        }

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $id
     * @return array
     */
    public static function getObjectifById($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'objectif_coach` WHERE `id_objectif_coach` = ' . (int)$id;

        return Db::getInstance()->getRow($sql);
    }

    /**
     * @param $id_employee
     * @param $dateBetween
     * @return array
     */
    public static function getObjectifCoachs($id_employee, $dateBetween)
    {
        $sql = 'SELECT `id_objectif_coach`, `somme`, `commentaire`, a.`id_employee`, `date_start`, `date_end`, `lastname`,
                `heure_absence`, `jour_absence`, `jour_ouvre`
                FROM `' . _DB_PREFIX_ . 'objectif_coach` AS a
                LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
                WHERE `date_start` BETWEEN ' . $dateBetween;

        if ($id_employee != 0) {
            $sql .= ' AND a.`id_employee` = ' . (int)$id_employee;
        }

        $sql .= ' ORDER BY `id_employee` ASC, `date_start` ASC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Retourne les classes CSS en fonction du pourcentage de CA atteint par l'employé
     * @param $objectifCoachs
     * @return mixed
     */
    public static function isProjectifAtteint($objectifCoachs)
    {
        if ($objectifCoachs) {

            foreach ($objectifCoachs as $objectifCoach => $objectif) {
                $dateBetween = '"' . $objectif['date_start'] . '" AND "' . $objectif['date_end'] . '"';
                $betweenDateNow = '"' . $objectif['date_start'] . '" AND "' . date('Y-m-d 23:59:59') . '"';
                $ca = CaTools::getCaCoachsTotal($objectif['id_employee'], 0, $dateBetween);

                $ajustement = CaTools::getAjustement($objectif['id_employee'], $dateBetween);
                $impaye = AjoutSomme::getImpaye($objectif['id_employee'], $dateBetween);
                $avoir = CaTools::getCaCoachsAvoir($objectif['id_employee'], $dateBetween);
                $jourTravaille = CaTools::getJourOuvreEmploye($objectif['id_employee'], $dateBetween);

                // Si la periode choisi n'est pas le mois complet, on prend l'intervalle réel
                if (($objectif['date_start'] != date('Y-m-01 00:00:00')) || ($objectif['date_end'] != date('Y-m-t 23:59:59')) ) {
                    $date_start = new DateTime($objectif['date_start']);
                    $date_end = new DateTime($objectif['date_end']);
                    $date_now = new DateTime(date('Y-m-d'));

                    if($date_end > $date_now) {
                        $betweenDateNow = '"' . $objectif['date_start'] . '" AND "' . date('Y-m-d 23:59:59') . '"';
                    }
                    if($date_end <= $date_now) {
                        $betweenDateNow = '"' . $objectif['date_start'] . '" AND "' . $objectif['date_end'] . '"';
                    }

                }

                $joursTravaille = CaTools::getNbOpenDays($betweenDateNow);

                $joursTravailleTotal = CaTools::getNbOpenDays($dateBetween);
                $joursAbsent = CaTools::getAbsenceEmployee($objectif['id_employee'], $dateBetween);
                $joursAbs = $joursAbsent['jours'];
                $caCoach = $ca + $ajustement - $impaye - $avoir;
                $objectifCoachs[$objectifCoach]['resteAFaire'] = $objectif['somme'] - $caCoach;
                $caCoach = $ca + $ajustement - $impaye - $avoir;

                $projectif = '';
                if ($joursTravaille != 0) {
                    $projectif = ($caCoach / ($joursTravaille - $joursAbsent['jours'])) * ($joursTravailleTotal - $joursAbsent['jours']);
                    $p = "($caCoach / $joursTravaille) * ($joursTravailleTotal - $joursAbs)";
                 }

                $objectifCoachs[$objectifCoach]['resteAFaire'] = $objectif['somme'] - $caCoach;

                $p = ($objectif['somme'] != 0) ? round(((100 * $projectif) / $objectif['somme']), 2) : '';
                $objectifCoachs[$objectifCoach]['pourcentDeObjectif'] = $p;
                $objectifCoachs[$objectifCoach]['caCoach'] = $caCoach;
                $objectifCoachs[$objectifCoach]['projectif'] = $projectif;
                $class = '';
                if ($p < 90) {
                    $class = 'danger';
                } elseif ($p >= 90 && $p < 100) {
                    $class = 'warning';
                } elseif ($p >= 100) {
                    $class = 'success';
                }
                $objectifCoachs[$objectifCoach]['class'] = $class;
            }
        }
        return $objectifCoachs;
    }

    /**
     * Retourne le chiffre d'affaire
     * @param int $idCoach
     * @param $idFilterCodeAction
     * @param $dateBetween
     * @return mixed
     */
    public static function getCaCoachsTotal($idCoach = 0, $idFilterCodeAction, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idCoach : '';

        $filterCodeAction = '';
        if ($idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.`id_code_action` != ' . pSQL(CaTools::getCodeActionByName('ABO'));
        } elseif ($idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.`id_code_action` = ' . (int)$idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2)) < 
                0 , 0, SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2))) as total
                FROM `' . _DB_PREFIX_ . 'orders` AS o';
        $sql .= ' WHERE `date_add` BETWEEN ';
        $sql .= $dateBetween;
        $sql .= ' AND `valid` = 1 ';
        $sql .= ' AND `current_state` != 460';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Soustraction CA propsect FID du CA Total
     * @param $data
     * @return string
     */
    public static function caProsp($data)
    {
        return ($data['caTotal'] > 0) ? $data['caTotal'] - $data['caDejaInscrit'] - $data['totalVenteGrPar'] - $data['caAvoir']: '';
    }

    /**
     * Retourne le pourcentage du CA prospect
     * @param $data
     * @return string
     */
    public static function pourcCaProspect($data)
    {
        if ($data['caTotal'] != 0) {
            return number_format(($data['CaProsp'] * 100) / $data['caAjuste'], 2) . ' %';
        }
        return '';
    }

    /**
     * Pourcentage du CA des prospects FID
     * @param $data
     * @return string
     */
    public static function pourcCaFID($data)
    {
        if ($data['caTotal'] != 0) {
            return round(($data['caDejaInscrit'] * 100) / $data['caAjuste'], 2) . ' %';
        }
        return '';
    }

    public static function getPanierMoyen($data)
    {
        if ($data['nbrVenteProsp'] != 0) {
            return round($data['CaProsp'] / $data['nbrVenteProsp'], 2);
        }
        return '';
    }

    public static function getPanierMoyenFid($data)
    {
        if ($data['nbrVenteFid'] != 0) {
            return round($data['caDejaInscrit'] / $data['nbrVenteFid'], 2);
        }
        return '';
    }

    /**
     * Retourne le nombre de vente par coach, code_action et intervalle de date
     * @param int $idFilterCoach
     * @param null $code_action
     * @param $dateBetween
     * @return mixed|string
     */
    public static function getNbrVentes($idFilterCoach = 0, $code_action = null, $dateBetween)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idFilterCoach : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = CaTools::getCodeActionByName($code_action);
            $sql_code_action = ' AND `id_code_action` = "' . (int)$code_action . '" ';
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_order
				FROM `' . _DB_PREFIX_ . 'orders` AS o
				WHERE `valid` = 1 ';
        $sql .= $sql_code_action;
        $sql .= $filterCoach;
        $sql .= ' AND o.`current_state` != 460'; // Commande gratuite
        $sql .= ' AND `date_add` BETWEEN ' . $dateBetween;
        $nbrVenteFID = Db::getInstance()->executeS($sql);

        $nbrRows = Db::getInstance()->getValue('SELECT FOUND_ROWS()');

        return ($nbrRows) ? $nbrRows : ''; // ($nbrVenteFID) ? $nbrVenteFID : '';
    }

    /**
     * Retourne la somme des commandes par parrainage
     * @param int $idFilterCoach
     * @param null $code_action
     * @param $dateBetween
     * @return mixed
     */
    public static function getParrainage($idFilterCoach = 0, $code_action = null, $dateBetween)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? " AND e . `id_employee` = '" . (int)$idFilterCoach . "'" : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = CaTools::getCodeActionByName($code_action);
            $sql_code_action = " AND o.`id_code_action` = '" . (int)$code_action . "'";
        }

        $sql = "SELECT SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`, 2)) as total 
                FROM `" . _DB_PREFIX_ . "orders` as o 
                LEFT JOIN `" . _DB_PREFIX_ . "customer` as c ON o.`id_customer` = c.`id_customer`
                LEFT JOIN `" . _DB_PREFIX_ . "employee` as e ON o.`id_employee` = e.`id_employee`";
        $sql .= ' WHERE o.`date_add` BETWEEN ' . $dateBetween;
        $sql .= $filterCoach;
        $sql .= $sql_code_action;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * @param int $idFilterCoach Id employé
     * @param null $code_action Id code action
     * @param null $current_state Statut de la commande
     * @param bool $totalMoney Somme totale des commande ou nombre total des commandes ?
     * @param bool $valid Commande valide ou pas
     * @param string $dateBetween Intervalle de date
     * @param $lang
     * @return mixed|string
     */
    public static function getNbrGrVentes(
        $idFilterCoach = 0,
        $code_action = null,
        $current_state = null,
        $totalMoney = false,
        $valid = false,
        $dateBetween,
        $lang
    )
    {
        $filterCoach = ($idFilterCoach != 0)
            ? " AND e.`id_employee` = '" . (int)$idFilterCoach . "'" : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = CaTools::getCodeActionByName($code_action);
            $sql_code_action = " AND o.`id_code_action` = '" . (int)$code_action . "'";
        }

        $filter_current_state = '';
        if ($current_state) {
            $filter_current_state = ' AND ( ';
            foreach ($current_state as $value) {
                $filter_current_state .= " o.`current_state` = '" . (int)$value . "' OR ";
            }
            $filter_current_state = Tools::substr($filter_current_state, 0, -3) . ' )';
        }

        $sqlTotal = ($totalMoney)
            ? "SELECT SUM(ROUND(o.`total_products` - o.`total_discounts_tax_excl`, 2)) as total "
            : "SELECT SQL_CALC_FOUND_ROWS o.`id_order` ";

        $sql = $sqlTotal . "
            FROM `" . _DB_PREFIX_ . "orders` as o
            LEFT JOIN `" . _DB_PREFIX_ . "customer` as c ON o.`id_customer` = c.`id_customer`
            LEFT JOIN `" . _DB_PREFIX_ . "customer_group` as cg ON c.`id_customer` = cg.`id_customer`
            LEFT JOIN `" . _DB_PREFIX_ . "group_lang` as gl ON cg.`id_group` = gl.`id_group` AND gl.`id_lang` = '" . (int)$lang . "'
            LEFT JOIN `" . _DB_PREFIX_ . "employee` as e ON gl.`id_employee` = e.`id_employee`";
        $sql .= ' WHERE o.`date_add` BETWEEN ' . $dateBetween;
        $sql .= ($valid) ? ' AND o.`valid` = 1 ' : '';
        $sql .= $filterCoach;
        $sql .= $sql_code_action;
        $sql .= $filter_current_state;

        $nbrGrVentes = Db::getInstance()->getValue($sql);
        $nbrRows = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

        return ($totalMoney) ? (($nbrGrVentes) ? $nbrGrVentes : '') : (($nbrRows) ? $nbrRows : '');
    }

    public static function getGroupeCoach($id_employee)
    {
        $sql = 'SELECT `id_group` 
                FROM `' . _DB_PREFIX_ . 'group_lang` 
                WHERE `id_employee` = ' . (int)$id_employee;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * Retourne la somme des ajustements affecté à un employé
     * @param int $id_employee
     * @param $getDateBetween
     * @return mixed
     */
    public static function getAjustement($id_employee = 0, $getDateBetween)
    {
        $filter = '';
        if ($id_employee != 0) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee;
        }

        $sql = 'SELECT SUM(`somme`) FROM `' . _DB_PREFIX_ . 'ajout_somme` 
                WHERE `date_ajout_somme` BETWEEN ' . $getDateBetween . '
                AND `impaye` IS NULL ';
        $sql .= $filter;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * Renvoi le code html de l'ensemble des doublons
     * @param $params
     * @return string
     */
    public static function doublons($params)
    {
        $retour = '';
        $customer = new Customer($params['id_customer']);

        $retour .= CaTools::isEmailCustomerUnique($customer);
        $retour .= CaTools::isCustomerSameNumberPhone($customer, $params);
        $retour .= CaTools::isCustomerHaveSameName($customer, $params);
        return $retour;
    }

    /**
     * Vérifie si l'email du client est unique, retourne le code html
     * @param Customer $customer
     * @return string
     */
    private static function isEmailCustomerUnique(Customer $customer)
    {
        $sql = 'SELECT COUNT(`email`) AS total 
                FROM `' . _DB_PREFIX_ . 'customer` WHERE `email` = "' . pSQL($customer->email) . '"';
        $req = Db::getInstance()->getValue($sql);

        $retour = '';
        if ($req > 1) {
            $retour = '<span class="text text-danger" title="Doublon Email">@</span>';
        }

        return $retour;
    }

    /**
     * Vérifie si le téléphone fixe ou mobile du client est unique, retourne le code html
     * @param Customer $customer
     * @param $params
     * @return string
     */
    private static function isCustomerSameNumberPhone(Customer $customer, $params)
    {
        $address = ($customer->getAddresses($params['lang']));
        $phone = '';
        $phone_mobile = '';
        $req_phone = '';
        $req_phone_mobile = '';

        if (isset($address[0])) {
            $phone = $address[0]['phone'];
            $phone_mobile = $address[0]['phone_mobile'];
        }

        if (!empty($phone)) {
            $sql = 'SELECT COUNT(`phone`) FROM `' . _DB_PREFIX_ . 'address` 
                WHERE `phone` = "' . pSQL($phone) . '"
                OR `phone_mobile` = "' . pSQL($phone) . '"
                ';

            $req_phone = Db::getInstance()->getValue($sql);
        }
        if (!empty($phone_mobile)) {
            $sql = 'SELECT COUNT(`phone`) FROM `' . _DB_PREFIX_ . 'address` 
                WHERE `phone` = "' . pSQL($phone_mobile) . '"
                OR `phone_mobile` = "' . pSQL($phone_mobile) . '"
                ';

            $req_phone_mobile = Db::getInstance()->getValue($sql);
        }
        $retour = '';
        if ($req_phone > 1 || $req_phone_mobile > 1) {
            $retour = '<i class="icon-phone text-danger" title="Doublon téléphone" ></i>';
        }

        return $retour;
    }

    /**
     * Est-ce que le nom prenom d'un client est unique ? retourne le code html
     * @param Customer $customer
     * @param $params
     * @return string
     */
    private static function isCustomerHaveSameName(Customer $customer, $params)
    {
        $sql = 'SELECT COUNT(`id_customer`) FROM `' . _DB_PREFIX_ . 'customer` 
                WHERE `lastname` = "' . pSQL($customer->lastname) . '" 
                AND `firstname` = "' . pSQL($customer->firstname) . '" ';

        $req = Db::getInstance()->getValue($sql);

        $retour = '';
        if ($req > 1) {
            $retour = '<i class="icon-group text-danger" title="Doublon Nom Client" ></i>';
        }

        return $retour;
    }

    /**
     * Retourne la somme des objectifs d'un employé dans l'intervalle de date donnée
     * @param $id_employee
     * @return array
     */
    public static function getObjectifCoach($id_employee)
    {
        $sql = 'SELECT SUM(`somme`) AS somme, MIN(`date_start`) AS date_start, MAX(`date_end`) AS date_end,
                `id_employee`
                FROM `' . _DB_PREFIX_ . 'objectif_coach` 
                WHERE `id_employee` = ' . (int)$id_employee . ' 
                AND NOW() > `date_start` 
                AND NOW() < `date_end` ';

        $req = Db::getInstance()->getRow($sql);

        return $req;
    }

    /**
     * Retourne le nombre de jour ouvré dans le mois
     * @param $dateBetween
     * @return int
     */
    public static function getNbOpenDays($dateBetween)
    {

        $date_start = strtotime(Tools::substr($dateBetween, 2, 10));
        $date_stop = strtotime(Tools::substr($dateBetween, 28, 10));

        $arr_bank_holidays = array(); // Tableau des jours feriés

        // On boucle dans le cas où l'année de départ serait différente de l'année d'arrivée
        $diff_year = date('Y', $date_stop) - date('Y', $date_start);
        for ($i = 0; $i <= $diff_year; $i++) {
            $year = (int)date('Y', $date_start) + $i;
            // Liste des jours feriés
            $arr_bank_holidays[] = '1_1_' . $year; // Jour de l'an
            $arr_bank_holidays[] = '1_5_' . $year; // Fete du travail
            $arr_bank_holidays[] = '8_5_' . $year; // Victoire 1945
            $arr_bank_holidays[] = '14_7_' . $year; // Fete nationale
            $arr_bank_holidays[] = '15_8_' . $year; // Assomption
            $arr_bank_holidays[] = '1_11_' . $year; // Toussaint
            $arr_bank_holidays[] = '11_11_' . $year; // Armistice 1918
            $arr_bank_holidays[] = '25_12_' . $year; // Noel

            // Récupération de paques. Permet ensuite d'obtenir le jour de l'ascension et celui de la pentecote
            $easter = easter_date($year);
            $arr_bank_holidays[] = date('j_n_' . $year, $easter + 86400); // Paques
            $arr_bank_holidays[] = date('j_n_' . $year, $easter + (86400 * 39)); // Ascension
            $arr_bank_holidays[] = date('j_n_' . $year, $easter + (86400 * 50)); // Pentecote
        }
        //print_r($arr_bank_holidays);
        $nb_days_open = 0;
        // Mettre <= si on souhaite prendre en compte le dernier jour dans le décompte
        while ($date_start <= $date_stop) {
            // Si le jour suivant n'est ni un dimanche (0) ou un samedi (6),
            // ni un jour férié, on incrémente les jours ouvrés
            if (!in_array(date('w', $date_start), array(0, 6))
                && !in_array(date('j_n_' . date('Y', $date_start), $date_start), $arr_bank_holidays)
            ) {
                $nb_days_open++;
            }
            $date_start = mktime(
                date('H', $date_start),
                date('i', $date_start),
                date('s', $date_start),
                date('m', $date_start),
                date('d', $date_start) + 1,
                date('Y', $date_start)
            );
        }
        return $nb_days_open;
    }

    /**
     * Retourne la somme des heures et jours d'absence d'un employé
     * @param int $id_employee
     * @param $dateBetween
     * @return array
     */
    public static function getAbsenceEmployee($id_employee = 0, $dateBetween)
    {
        $filter = '';
        if ($id_employee != 0) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee;
        }
        $sql = 'SELECT SUM(`heure_absence`) as heures, SUM(`jour_absence`) AS jours
                FROM `' . _DB_PREFIX_ . 'objectif_coach`
                WHERE `date_start` BETWEEN ' . $dateBetween . '
                AND `date_end` BETWEEN ' . $dateBetween;
        $sql .= $filter;

        $req = Db::getInstance()->getRow($sql);

        return $req;
    }

    /**
     * Calcule le montant de la prime fichier par employé
     * @param int $id_employee
     * @param $dateBetween
     * @return float|string
     */
    public static function primeFichier($id_employee = 0, $dateBetween)
    {
        $taux = Configuration::get('CDMODULECA_PRIME_FICHIER');
        $pros_jour = Configuration::get('CDMODULECA_PROSPECTS_JOUR');
        $pros_heure = Configuration::get('CDMODULECA_PROSPECTS_HEURE');
        $jour_ouvre = CaTools::getJourOuvreEmploye($id_employee, $dateBetween);
        $nbr_jours_ouvre = (empty($jour_ouvre)) ? CaTools::getNbOpenDays($dateBetween) : $jour_ouvre;
        $nbr_prospects = ProspectAttribueClass::getNbrProspectsAttriByCoach($id_employee, $dateBetween);
        $absence = CaTools::getAbsenceEmployee($id_employee, $dateBetween);

        if ($nbr_prospects == 0) {
            return '';
        }

        $prime = ((($pros_jour * ($nbr_jours_ouvre - $absence['jours'])) - ($absence['heures'] * $pros_heure))
                - $nbr_prospects) * $taux;

        return round($prime, 2);
    }

    /**
     * Crée ou met à jour le compteur d'appel
     * @param $id
     */
    public static function setCompteurAppels($id)
    {
        $compteur = AppelClass::getCompteur((int)$id, date('Y-m-d'));
        // Si le compteur n'existe pas, on en créé un
        if (empty($compteur['compteur'])) {
            $appels = new AppelClass();
            $appels->compteur = 1;
            $appels->id_employee = (int)$id;
            $appels->save();
            setcookie('appelKeyyo', '1', strtotime(date('Y-m-d 23:59:59')));
        } else {
            // Mise à jour du compteur
            $compteur['compteur']++;
            Db::getInstance()->update(
                'appel',
                array('compteur' => $compteur['compteur']),
                '`id_appel` = ' . (int)$compteur['id_appel']
            );
            setcookie('appelKeyyo', $compteur['compteur']++, strtotime(date('Y-m-d 23:59:59')));
        }
    }

    /**
     * Fait la somme des avoirs
     * @param $idCoach
     * @param $dateBetween
     * @return mixed
     */
    public static function getCaCoachsAvoir($idCoach = 0, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND `id_employee` = ' . (int)$idCoach : '';

        $sql = 'SELECT SUM(`total_products_tax_excl`) FROM `' . _DB_PREFIX_ . 'order_slip` AS os
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` AS o ON o.`id_order` = os.`id_order` 
                WHERE os.date_add BETWEEN ' . $dateBetween;
        $sql .= $filterCoach;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * Fait la somme des jours ouvrés d'un employé
     * @param $id_employee
     * @param $dateBetween
     * @return mixed
     */
    public static function getJourOuvreEmploye($id_employee, $dateBetween)
    {
        $sql = 'SELECT SUM(`jour_ouvre`) FROM `' . _DB_PREFIX_ . 'objectif_coach` 
                WHERE `id_employee` = ' . (int)$id_employee . ' 
                AND `date_start` BETWEEN ' . $dateBetween;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    /**
     * Convertir la date en français au format anglais
     * @param $date_ajout
     * @return string
     */
    public static function convertDate($date_ajout)
    {
        $tabDate = explode('/', $date_ajout);
        return date('Y-m-d', strtotime((int)$tabDate[2] . '-' . (int)$tabDate['1'] . '-' . (int)$tabDate[0]));
    }

    public static function getOrderDetailsCoach($id_order)
    {
        $sql = 'SELECT `code_action`, `coach`, `id_code_action`, `id_employee` FROM `' . _DB_PREFIX_ . 'orders`
                WHERE `id_order` = "' . (int)$id_order . '" ';
        $req = Db::getInstance()->getRow($sql);

        return $req;
    }
}
