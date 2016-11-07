<?php

require_once(dirname(__FILE__) . '/AppelClass.php');

class CaTools
{
    public static function getEmployees($active = null, $id = null)
    {
        $sql = 'SELECT gl.`id_group`,e.`id_employee`, e.`firstname`, e.`lastname`
			FROM `' . _DB_PREFIX_ . 'employee` AS e ';
        $sql .= 'LEFT JOIN `ps_group_lang` AS gl ON e.`id_employee` = gl.`id_employee` ';
        $sql .= ($active == 'on') ? 'WHERE e.`active` = 1 ' : '';
        $sql .= ($id) ? ' WHERE e.`id_employee` = ' . (int)$id : '';
        $sql .= ' ORDER BY e.`lastname` ASC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $idCoach
     * @param $idFilterCodeAction
     * @param $dateBetween
     * @return mixed
     */
    public static function getCaCoachsTotal($idCoach = 0, $idFilterCodeAction, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . (int)$idCoach : '';

        $filterCodeAction = '';
        if ($idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.id_code_action != ' . pSQL(CaTools::getCodeActionByName('ABO'));
        } elseif ($idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.id_code_action = ' . (int)$idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) < 
                0 , 0, SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2))) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ';
        $sql .= $dateBetween;
        $sql .= ' AND valid = 1 ';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    public static function getCaCoachsRembourse($idCoach = 0, $idFilterCodeAction, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . (int)$idCoach : '';

        $filterCodeAction = '';
        if ($idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.id_code_action != ' . pSQL(CaTools::getCodeActionByName('ABO'));
        } elseif ($idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.id_code_action = ' . (int)$idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) < 
                0 , 0, SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2))) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ';
        $sql .= $dateBetween;
        $sql .= ' AND valid = 1 ';
        $sql .= ' AND (current_state = 7)';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    public static function getCodeActionByName($name)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE name = "' . pSQL($name) . '"';

        return Db::getInstance()->getValue($sql);
    }

    public static function getCaDejaInscrit($idFilterCoach = 0, $dateBetween)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . (int)$idFilterCoach : '';

        $sql = 'SELECT ROUND(o.total_products - o.total_discounts_tax_excl,2) AS total,
                IF((SELECT so.id_order FROM `ps_orders` so WHERE so.id_customer = o.id_customer 
                AND so.id_order < o.id_order LIMIT 1) > 0, 1, 0) as notNew
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1';
        $sql .= $filterCoach;
        $sql .= ' AND date_add BETWEEN ' . $dateBetween;
        $caFID = Db::getInstance()->executeS($sql);

        $total = '';
        foreach ($caFID as $ca) {
            $total += ($ca['notNew']) ? $ca['total'] : 0;
        }

        return $total;
    }

    public static function getCaDeduit($idFilterCoach = 0, $dateBetween)
    {
        $listStatuts = explode(',', Configuration::get('CDMODULECA_ORDERS_STATE'));
        $sqlStatuts = ' AND ( ';
        foreach ($listStatuts as $statut) {
            $sqlStatuts .= ' current_state = ' . pSQL($statut) . ' OR ';
        }
        $sqlStatuts = substr($sqlStatuts, 0, -3) . ')';

        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . (int)$idFilterCoach : '';

        $sql = 'SELECT SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ' . $dateBetween;
        $sql .= $filterCoach;
        $sql .= $sqlStatuts;

        return Db::getInstance()->getValue($sql);
    }

    public static function getNumberCommande($idCoach = 0, $idCodeAction = 0, $current_state = null, $dateBetween)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . (int)$idCoach : '';

        $filterCodeAction = ($idCodeAction != 0)
            ? ' AND id_code_action = ' . (int)$idCodeAction : '';
        $filter_current_state = '';
        if ($current_state) {
            $filter_current_state = ' AND ( ';
            foreach ($current_state as $value) {
                $filter_current_state .= " o.current_state != '" . (int)$value . "' AND ";
            }
            $filter_current_state = substr($filter_current_state, 0, -4) . ' )';
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_order
                FROM ' . _DB_PREFIX_ . 'orders AS o
                WHERE valid = 1 ';
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;
        $sql .= $filter_current_state;
        $sql .= ' AND date_add BETWEEN ' . $dateBetween;

        Db::getInstance()->executeS($sql);
        $nbr = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
        $nbr = ($nbr) ? $nbr : '';

        return $nbr;
    }

    public static function getCodeAction($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE id_code_action = ' . (intval($id));

        return Db::getInstance()->getRow($sql);
    }

    public static function getAllGroupeCodesAction()
    {
        $sql = 'SELECT DISTINCT groupe FROM `' . _DB_PREFIX_ . 'code_action`
        ';
        $groupes = Db::getInstance()->executeS($sql);

        $listGroupes = array();
        foreach ($groupes as $groupe) {
            $listGroupes[] = CaTools::getCodeAction($groupe['groupe']);
        }

        return $listGroupes;
    }

    public static function getAjoutSommeById($id)
    {
        $sql = 'SELECT * FROM ps_ajout_somme WHERE id_ajout_somme = ' . (int)$id;

        return Db::getInstance()->getRow($sql);
    }

    public static function getAjoutSomme($id_employee, $dateBetween)
    {
        $sql = 'SELECT id_ajout_somme, somme, commentaire, a.id_employee, date_add, lastname
                FROM `ps_ajout_somme` AS a
                LEFT JOIN `ps_employee` AS e ON a.id_employee = e.id_employee
                WHERE date_add BETWEEN ' . $dateBetween;

        if ($id_employee != 0) {
            $sql .= ' AND a.id_employee = ' . (int)$id_employee;
        }

        return Db::getInstance()->executeS($sql);
    }

    public static function getObjectifById($id)
    {
        $sql = 'SELECT * FROM ps_objectif_coach WHERE id_objectif_coach = ' . (int)$id;

        return Db::getInstance()->getRow($sql);
    }

    public static function getObjectifCoachs($id_employee, $dateBetween)
    {
        $sql = 'SELECT id_objectif_coach, somme, commentaire, a.id_employee, date_start, date_end, lastname,
                heure_absence, jour_absence, jour_ouvre
                FROM `ps_objectif_coach` AS a
                LEFT JOIN `ps_employee` AS e ON a.id_employee = e.id_employee
                WHERE date_start BETWEEN ' . $dateBetween;

        if ($id_employee != 0) {
            $sql .= ' AND a.id_employee = ' . (int)$id_employee;
        }

        $sql .= ' ORDER BY id_employee ASC, date_start ASC';

        return Db::getInstance()->executeS($sql);
    }

    public static function isObjectifAtteint($objectifCoachs)
    {
        if ($objectifCoachs) {

            foreach ($objectifCoachs as $objectifCoach => $objectif) {
                $dateBetween = '"' . $objectif['date_start'] . '" AND "' . $objectif['date_end'] . '"';

                $caCoach = CaTools::getCaCoachsTotal($objectif['id_employee'], 0, $dateBetween);

                $p = ($objectif['somme'] != 0) ? round(((100 * $caCoach) / $objectif['somme']), 2) : '';
                $objectifCoachs[$objectifCoach]['pourcentDeObjectif'] = $p;
                $objectifCoachs[$objectifCoach]['caCoach'] = $caCoach;
                $class = '';
                if ($p < 50) {
                    $class = 'danger';
                } elseif ($p >= 50 && $p < 100) {
                    $class = 'warning';
                } elseif ($p >= 100) {
                    $class = 'success';
                }
                $objectifCoachs[$objectifCoach]['class'] = $class;
            }
        }

        return $objectifCoachs;
    }

    public static function caProsp($data)
    {
        return ($data['caTotal']) ? $data['caTotal'] - $data['caDejaInscrit'] : '';
    }

    public static function PourcCaProspect($data)
    {
        if ($data['caTotal'] != 0) {
            return number_format(($data['CaProsp'] * 100) / $data['caAjuste'], 2) . ' %';
        }
        return '';
    }

    public static function PourcCaFID($data)
    {
        if ($data['caTotal'] != 0) {
            return number_format(($data['caDejaInscrit'] * 100) / $data['caAjuste'], 2) . ' %';
        }
        return '';
    }

    public static function getPanierMoyen($data)
    {
        if ($data['NbreVentesTotal'] != 0) {
            return $data['caAjuste'] / $data['NbreVentesTotal'];
        }
        return '';
    }

    public static function getNbrVentes($idFilterCoach = 0, $code_action = null, $dateBetween)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . (int)$idFilterCoach : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = CaTools::getCodeActionByName($code_action);
            $sql_code_action = ' AND id_code_action = "' . (int)$code_action . '" ';
        }


        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_order
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1 ';
        $sql .= $sql_code_action;
        $sql .= $filterCoach;
        $sql .= ' AND date_add BETWEEN ' . $dateBetween;
        $nbrVenteFID = Db::getInstance()->executeS($sql);


        $nbrRows = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

        return ($nbrRows) ? $nbrRows : ''; // ($nbrVenteFID) ? $nbrVenteFID : '';
    }

    public static function getNbrGrVentes($idFilterCoach = 0, $code_action = null, $current_state = null,
                                          $totalMoney = false, $valid = false, $dateBetween, $lang)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? " AND e . id_employee = '" . (int)$idFilterCoach . "'" : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = CaTools::getCodeActionByName($code_action);
            $sql_code_action = " AND o . id_code_action = '" . (int)$code_action . "'";
        }

        $filter_current_state = '';
        if ($current_state) {
            $filter_current_state = ' AND ( ';
            foreach ($current_state as $value) {
                $filter_current_state .= " o . current_state = '" . (int)$value . "' OR ";
            }
            $filter_current_state = substr($filter_current_state, 0, -3) . ' )';
        }

        $sqlTotal = ($totalMoney)
            ? "SELECT SUM(ROUND(o . total_products - o . total_discounts_tax_excl, 2)) as total "
            : "SELECT SQL_CALC_FOUND_ROWS o . id_order ";

        $sql = $sqlTotal . "
            FROM ps_orders as o
            LEFT JOIN ps_customer as c ON o . id_customer = c . id_customer
            LEFT JOIN ps_customer_group as cg ON c . id_customer = cg . id_customer
            LEFT JOIN ps_group_lang as gl ON cg . id_group = gl . id_group AND gl.id_lang = '" . $lang . "'
            LEFT JOIN ps_employee as e ON gl . id_employee = e . id_employee";
        $sql .= ' WHERE o.date_add BETWEEN ' . $dateBetween;
        $sql .= ($valid) ? ' AND o.valid = 1 ' : '';
        $sql .= $filterCoach;
        $sql .= $sql_code_action;
        $sql .= $filter_current_state;

        $nbrGrVentes = Db::getInstance()->getValue($sql);
        $nbrRows = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

        return ($totalMoney) ? (($nbrGrVentes) ? $nbrGrVentes : '') : (($nbrRows) ? $nbrRows : '');
    }

    public static function getGroupeCoach($id_employee)
    {
        $sql = 'SELECT `id_group` FROM `ps_group_lang` WHERE `id_employee` = ' . (int)$id_employee;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    public static function getAjustement($id_employee = 0, $getDateBetween)
    {
        $filter = '';
        if ($id_employee != 0) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee;
        }

        $sql = 'SELECT SUM(`somme`) FROM `ps_ajout_somme` 
                WHERE `date_add` BETWEEN ' . $getDateBetween;
        $sql .= $filter;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    public static function doublons($params)
    {
        $retour = '';
        $customer = new Customer($params['id_customer']);

        $retour .= CaTools::isEmailCustomerUnique($customer);
        $retour .= CaTools::isCustomerSameNumberPhone($customer, $params);
        $retour .= CaTools::isCustomerHaveSameName($customer, $params);
        return $retour;
    }

    private static function isEmailCustomerUnique(Customer $customer)
    {
        $sql = ' SELECT COUNT(`email`) AS total FROM `ps_customer` WHERE `email` = "' . pSQL($customer->email) . '"';
        $req = Db::getInstance()->getValue($sql);

        $retour = '';
        if ($req > 1) {
            $retour = '<span class="text text-danger" title="Doublon Email">@</span>';
        }

        return $retour;
    }

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
            $sql = 'SELECT COUNT(`phone`) FROM `ps_address` 
                WHERE `phone` = "' . pSQL($phone) . '"
                OR `phone_mobile` = "' . pSQL($phone) . '"
                ';

            $req_phone = Db::getInstance()->getValue($sql);
        }
        if (!empty($phone_mobile)) {
            $sql = 'SELECT COUNT(`phone`) FROM `ps_address` 
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

    private static function isCustomerHaveSameName(Customer $customer, $params)
    {
        $sql = 'SELECT COUNT(`id_customer`) FROM `ps_customer` 
                WHERE `lastname` = "' . pSQL($customer->lastname) . '" 
                AND `firstname` = "' . pSQL($customer->firstname) . '" ';

        $req = Db::getInstance()->getValue($sql);

        $retour = '';
        if ($req > 1) {
            $retour = '<i class="icon-group text-danger" title="Doublon Nom Client" ></i>';
        }

        return $retour;
    }

    public static function getObjectifCoach($id_employee)
    {
        $sql = 'SELECT SUM(`somme`) AS somme, MIN(`date_start`) AS date_start, MAX(`date_end`) AS date_end,
                `id_employee`
                FROM `ps_objectif_coach` 
                WHERE `id_employee` = ' . (int)$id_employee . ' 
                AND NOW() > `date_start` 
                AND NOW() < `date_end` ';

        $req = Db::getInstance()->getRow($sql);

        return $req;
    }

    public static function get_nb_open_days($dateBetween)
    {

        $date_start = strtotime(substr($dateBetween, 2, 10));
        $date_stop = strtotime(substr($dateBetween, 28, 10));

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
            // Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour férié, on incrémente les jours ouvrés
            if (!in_array(date('w', $date_start), array(0, 6))
                && !in_array(date('j_n_' . date('Y', $date_start), $date_start), $arr_bank_holidays)
            ) {
                $nb_days_open++;
            }
            $date_start = mktime(date('H', $date_start), date('i', $date_start), date('s', $date_start), date('m', $date_start), date('d', $date_start) + 1, date('Y', $date_start));
        }
        return $nb_days_open;
    }

    public static function getAbsenceEmployee($id_employee = 0, $dateBetween)
    {
        $filter = '';
        if ($id_employee != 0) {
            $filter = ' AND `id_employee` = ' . (int)$id_employee;
        }
        $sql = 'SELECT SUM(`heure_absence`) as heures, SUM(`jour_absence`) AS jours
                FROM `ps_objectif_coach`
                WHERE `date_start` BETWEEN ' . $dateBetween . '
                AND `date_end` BETWEEN ' . $dateBetween;
        $sql .= $filter;

        $req = Db::getInstance()->getRow($sql);

        return $req;
    }

    public static function primeFichier($id_employee = 0, $dateBetween)
    {
        $taux = Configuration::get('CDMODULECA_PRIME_FICHIER');
        $pros_jour = Configuration::get('CDMODULECA_PROSPECTS_JOUR');
        $pros_heure = Configuration::get('CDMODULECA_PROSPECTS_HEURE');
        $jour_ouvre = CaTools::getJourOuvreEmploye($id_employee, $dateBetween);
        $nbr_jours_ouvre = (empty($jour_ouvre)) ? CaTools::get_nb_open_days($dateBetween) : $jour_ouvre;
        $nbr_prospects = ProspectAttribueClass::getNbrProspectsAttriByCoach($id_employee, $dateBetween);
        $absence = CaTools::getAbsenceEmployee($id_employee, $dateBetween);

        if ($nbr_prospects == 0) {
            return '';
        }

        $prime = ((($pros_jour * ($nbr_jours_ouvre - $absence['jours'])) - ($absence['heures'] * $pros_heure))
                - $nbr_prospects) * $taux;

        return round($prime, 2);
    }

    public static function setCompteurAppels($id)
    {
        $compteur = AppelClass::getCompteur((int)$id, date('Y-m-d'));
        if (empty($compteur['compteur'])) {
            $appels = new AppelClass();
            $appels->compteur = 1;
            $appels->id_employee = (int)$id;
            $appels->save();
            setcookie('appelKeyyo', '1', strtotime(date('Y-m-d 23:59:59')));
        } else {
            $compteur['compteur']++;
            Db::getInstance()->update('appel', array('compteur' => $compteur['compteur']),
                '`id_appel` = ' . (int)$compteur['id_appel']);
            setcookie('appelKeyyo', $compteur['compteur']++, strtotime(date('Y-m-d 23:59:59')));
        }
    }

    public static function getCaCoachsAvoir($idCoach, $dateBetween)
    {
        $sql = 'SELECT SUM(amount) FROM `ps_order_slip` AS os
                LEFT JOIN `ps_orders` AS o ON o.`id_order` = os.`id_order` 
                WHERE os.date_add BETWEEN ' . $dateBetween . '
                AND o.`id_employee` = ' . $idCoach;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    private static function getJourOuvreEmploye($id_employee, $dateBetween)
    {
        $sql = 'SELECT SUM(`jour_ouvre`) FROM `ps_objectif_coach` 
                WHERE `id_employee` = ' . (int)$id_employee . ' 
                AND `date_start` BETWEEN ' . $dateBetween;

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }
}
