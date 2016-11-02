<?php

require_once(dirname(__FILE__) . '/../../classes/ProspectClass.php');
require_once(dirname(__FILE__) . '/../../classes/ProspectAttribueClass.php');
require_once(dirname(__FILE__) . '/../../classes/CaTools.php');

class AdminProspectsController extends ModuleAdminController
{
    protected $html;
    protected $smarty;
    protected $path_tpl;
    protected $employesActif;

    public function __construct()
    {
        $this->module = 'cdmoduleca';
        $this->bootstrap = true;
        $this->className = 'AdminProspects';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/prospects/';
        $this->employesActif = $this->context->cookie->cdmoduleca_admin_prospect_employe_actif;
        parent::__construct();
    }

    public function initContent()
    {
        $isAllow = $this->module->viewAllCoachs[$this->context->employee->id_profile];
        $linkFilterCoachs = AdminController::$currentIndex . '&module=' . $this->module->name
            . '&token=' . Tools::getValue('token');
        $actif = (empty($this->employesActif)) ? null : 'on';
        $listCoaches = CaTools::getEmployees($actif);
        $prospectsIsoles = $this->getProspectsIsole();
        $this->generateForm();

        $this->smarty->assign(array(
            'nbr_prospects' => $this->nbrNouveauProspects(),
            'prospects_by_coach' => $this->listProspectsByCoach($isAllow, $this->context->employee->id),
            'nbrProspectsIsoles' => count($prospectsIsoles),
            'prospectsIsoles' => $prospectsIsoles,
            'coachs' => $listCoaches,
            'isAllow' => $isAllow,
            'linkFilter' => $linkFilterCoachs,
            'employeActif' => $this->employesActif,
            'confirmation' => $this->confirmations,
            'errors' => implode('<br>', $this->errors)
        ));

        $this->html .= $this->displayCalendar();
        $this->html .= $this->smarty->fetch($this->path_tpl . 'prospectsForm.tpl');
        $this->html .= $this->smarty->fetch($this->path_tpl . 'prospectsByCoach.tpl');
        $this->html .= $this->smarty->fetch($this->path_tpl . 'prospectsList.tpl');
        $this->content = $this->html;

        return parent::initContent();
    }

    public function postProcess()
    {
        $this->processDateRange();
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('submitEmployeActif')) {
                $this->setEmployeActif(Tools::getValue('employeActif'));
            }
            if (Tools::isSubmit('submitEmployes')) {
                $this->setEmployesAttribue();
            } elseif (Tools::isSubmit('mod_pa')) {
                $this->displayUpdateProspectsAttribue();
            } elseif (Tools::isSubmit('del_pa')) {
                $this->deleteProspectsAttribue();
            }
        }
        if (Tools::isSubmit('view_pa')) {
            $this->viewProspectsAttribue();
        }

        return parent::postProcess();
    }

    private function setEmployesAttribue()
    {
        $isOk = true;
        if (!Validate::isDateFormat(Tools::getValue('p_date_start')) ||
            !Validate::isDateFormat(Tools::getValue('p_date_end'))
        ) {
            $isOk = false;
        }
        $date_debut = Tools::getValue('p_date_start');
        $date_fin = Tools::getValue('p_date_end');

        if ($date_debut > $date_fin) {
            $isOk = false;
        }
        // Modification d'un enregistrement prospects attribué
        if ($isOk && Tools::isSubmit('pa_id_pa')) {
            $id_pa = (Validate::isInt((int)Tools::getValue('pa_id_pa'))) ? (int)Tools::getValue('pa_id_pa') : false;
            $id_em = (Validate::isInt((int)Tools::getValue('pa_id_employee'))) ? (int)Tools::getValue('pa_id_employee') : false;
            if ($id_pa && $id_em) {
                if (ProspectAttribueClass::isExist($id_pa)) {
                    $pa = new ProspectAttribueClass($id_pa);
                    $pa->date_debut = $date_debut;
                    $pa->date_fin = $date_fin;
                    $this->changeGroupProspects($pa, $this->module->getGroupeEmployee($id_em));
                    $pa->id_employee = $id_em;
                    $pa->update();
                };
            }
        } elseif (Tools::isSubmit('pi_id_employee')) {
            $id_employe = (int)Tools::getValue('pi_id_employee');
            $nb_pros = (int)Tools::getValue('pi_nbr_pr');
            $nbrProspect = count($this->getProspectsIsole());

            if ($nb_pros >= $nbrProspect) {
                $nb_pros = $nbrProspect;
            }
            $attriProspect = new ProspectAttribueClass();
            $attriProspect->date_debut = $date_debut;
            $attriProspect->date_fin = $date_fin;
            $attriProspect->id_employee = $id_employe;
            $attriProspect->nbr_prospect_attribue = $nb_pros;
            $attriProspect->save();
            $this->attribuProspectsIsoles($attriProspect, $this->module->getGroupeEmployee($id_employe));

            // Enregistrement d'une nouvelle ligne
        } elseif ($isOk) {
            foreach ($_POST as $key => $nbrProspect) {
                if (substr($key, 0, 3) == 'em_' && !empty($nbrProspect) && $isOk) {
                    if (!Validate::isInt(str_replace('em_', '', $key)) ||
                        !Validate::isInt($nbrProspect) ||
                        $nbrProspect <= 0
                    ) {
                        $isOk = false;
                    } else {
                        $id_employe = str_replace('em_', '', $key);
                        $attriProspect = new ProspectAttribueClass();
                        $attriProspect->date_debut = $date_debut;
                        $attriProspect->date_fin = $date_fin;
                        $attriProspect->id_employee = $id_employe;
                        $attriProspect->nbr_prospect_attribue = $nbrProspect;
                        $isOk = $this->attribuProspects($attriProspect, $this->module->getGroupeEmployee($id_employe));
                    }
                }
            }
        }
        if ($isOk) {
            $this->confirmations = $this->module->l('Enregistrement éffectué');
        } else {
            $this->errors[] = $this->module->l('Erreur lors de l\'enregistrement');
        }
    }

    private function displayUpdateProspectsAttribue()
    {
        $id = (Validate::isInt((int)Tools::getValue('pa_id_pa'))) ? (int)Tools::getValue('id_pa') : false;
        if (ProspectAttribueClass::isExist($id)) {
            $pa = new ProspectAttribueClass($id);
            $this->smarty->assign(array(
                'pa' => $pa
            ));
        }
    }

    private function setEmployeActif($state)
    {
        if ($state == 'on') {
            $this->employesActif = 'checked';
        } else {
            $this->employesActif = '';
        }
        $this->context->cookie->cdmoduleca_admin_prospect_employe_actif = $this->employesActif;
    }

    private function generateForm()
    {
        $linkForm = AdminController::$currentIndex . '&token=' . Tools::getValue('token');
        $employes = $this->getEmployeesCoach();
        $this->smarty->assign(array(
            'employes' => $employes,
            'linkForm' => $linkForm
        ));
    }

    private function getEmployeesCoach()
    {
        $actif = (empty($this->employesActif)) ? '' : ' WHERE e.`active` = 1';
        $sql = '
            SELECT gl.`id_group`, e.`id_employee`, e.`lastname`, e.`firstname`, pa.`nbr_prospect_attribue`,pa.`date_debut`,
             pa.`date_fin`,
             (  SELECT COUNT(ppr.`id_prospect_attribue`)
                FROM `ps_prospect` as ppr
                LEFT JOIN `ps_prospect_attribue` AS ppa ON ppr.`id_prospect_attribue` = ppa.`id_prospect_attribue`
                WHERE e.`id_employee` = ppa.`id_employee`
                AND ppr.`traite` !=1
                AND ppr.`injoignable` !=1) AS total_prospect
                
            FROM `ps_employee` AS e 
            LEFT JOIN `ps_prospect_attribue` AS pa ON e.`id_employee` = pa.`id_employee`
            LEFT JOIN `ps_prospect` AS p ON pa.`id_prospect_attribue` = p.`id_prospect_attribue`
            LEFT JOIN `ps_group_lang` as gl ON gl.`id_employee` = e.`id_employee`
            ';
        $sql .= $actif;
        $sql .= ' GROUP BY e.`id_employee`';
        $sql .= ' ORDER BY e.`lastname` ASC ';


        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    private function attribuProspects(ProspectAttribueClass $ap, $id_group)
    {
        $index_id = ProspectClass::getLastCustomer();
        $nbr_prospects_disponible = $this->nbrProspectsDisponible($index_id);
        $ap->add();
        if ($nbr_prospects_disponible >= $ap->nbr_prospect_attribue) {
            $prospects = ProspectClass::getAllProspectsGroup(1, $ap->nbr_prospect_attribue, $index_id);
            $this->addProspects($prospects, $id_group, $ap);
        } elseif ($nbr_prospects_disponible == 0) {
            $index_id = 0;
            $prospects = ProspectClass::getAllProspectsGroup(1, $ap->nbr_prospect_attribue, $index_id);
            $this->addProspects($prospects, $id_group, $ap);
        } else {
            $this->errors[] = $this->module->l('Il n\'y a pas assez de prospects disponible');
            $ap->delete();
            return false;
        }
        return true;
    }

    private function addProspects($prospects, $id_group, ProspectAttribueClass $ap)
    {
        foreach ($prospects as $prospect) {
            $c = new Customer($prospect['id_customer']);
            $g = $c->getGroups();
            unset($g[array_search('1', $g)]);
            $g[] = $id_group;
            $c->updateGroup($g);
            $p = new ProspectClass();
            $p->id_customer = $c->id;
            $p->id_prospect_attribue = $ap->id;
            $p->traite = 'Prospect';
            $p->injoignable = 'Non';
            $p->add();
        }
    }

    private function nbrNouveauProspects()
    {
        $nbr_nouveaux_prospects = 0;
        $index_prospects = ProspectClass::getLastCustomer();
        if ($index_prospects != null) {
            $nbr_nouveaux_prospects = $this->getNbrNouveauProspects($index_prospects);
        }

        return $nbr_nouveaux_prospects;
    }

    public function displayCalendar()
    {
        return AdminProspectsController::displayCalendarForm(array(
            'Calendar' => $this->l('Calendrier', 'AdminCaLetSens'),
            'Day' => $this->l('Jour', 'AdminCaLetSens'),
            'Month' => $this->l('Mois', 'AdminCaLetSens'),
            'Year' => $this->l('Année', 'AdminCaLetSens'),
            'From' => $this->l('Du', 'AdminCaLetSens'),
            'To' => $this->l('Au', 'AdminCaLetSens'),
            'Save' => $this->l('Enregistrer', 'AdminCaLetSens')
        ), $this->token);
    }

    public function displayCalendarForm($translations, $token, $action = null, $table = null, $identifier = null, $id = null)
    {

        $context = $this->context;

        $context->controller->addJqueryUI('ui.datepicker');
        if ($identifier === null && Tools::getValue('module')) {
            $identifier = 'module';
            $id = Tools::getValue('module');
        }

        $action = Context::getContext()->link->getAdminLink('AdminProspects');
        $action .= ($action && $table ? '&' . Tools::safeOutput($action) : '');
        $action .= ($identifier && $id ? '&' . Tools::safeOutput($identifier) . '=' . (int)$id : '');
        $module = Tools::getValue('module');
        $action .= ($module ? '&module=' . Tools::safeOutput($module) : '');
        $action .= (($id_product = Tools::getValue('id_product')) ? '&id_product=' . Tools::safeOutput($id_product) : '');
        $this->smarty->assign(array(
            'current' => self::$currentIndex,
            'token' => $token,
            'action' => $action,
            'table' => $table,
            'identifier' => $identifier,
            'id' => $id,
            'translations' => $translations,
            'datepickerFrom' => Tools::getValue('datepickerFrom', $context->employee->stats_date_from),
            'datepickerTo' => Tools::getValue('datepickerTo', $context->employee->stats_date_to)
        ));

        $tpl = $this->smarty->fetch($this->path_tpl . '../ca/calendar/form_date_range_picker.tpl');
        return $tpl;
    }

    public function processDateRange()
    {
        if (Tools::isSubmit('submitDatePicker')) {
            if ((!Validate::isDate($from = Tools::getValue('datepickerFrom')) || !Validate::isDate($to = Tools::getValue('datepickerTo'))) || (strtotime($from) > strtotime($to)))
                $this->errors[] = Tools::displayError('The specified date is invalid.');
        }
        if (Tools::isSubmit('submitDateDay')) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        if (Tools::isSubmit('submitDateDayPrev')) {
            $yesterday = time() - 60 * 60 * 24;
            $from = date('Y-m-d', $yesterday);
            $to = date('Y-m-d', $yesterday);
        }
        if (Tools::isSubmit('submitDateMonth')) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }
        if (Tools::isSubmit('submitDateMonthPrev')) {
            $m = (date('m') == 1 ? 12 : date('m') - 1);
            $y = ($m == 12 ? date('Y') - 1 : date('Y'));
            $from = $y . '-' . $m . '-01';
            $to = $y . '-' . $m . date('-t', mktime(12, 0, 0, $m, 15, $y));
        }
        if (Tools::isSubmit('submitDateYear')) {
            $from = date('Y-01-01');
            $to = date('Y-12-31');
        }
        if (Tools::isSubmit('submitDateYearPrev')) {
            $from = (date('Y') - 1) . date('-01-01');
            $to = (date('Y') - 1) . date('-12-31');
        }
        if (isset($from) && isset($to) && !count($this->errors)) {
            $this->context->employee->stats_date_from = $from;
            $this->context->employee->stats_date_to = $to;
            $this->context->employee->update();
            if (!$this->isXmlHttpRequest())
                Tools::redirectAdmin($_SERVER['REQUEST_URI']);
        }
    }

    public function getNbrNouveauProspects($id_customer)
    {
        $sql = 'SELECT COUNT(c.`id_customer`) AS total
                FROM `ps_customer` as c 
                LEFT JOIN `ps_customer_group` AS cg ON c.`id_customer` = cg.`id_customer`
                WHERE c.`id_customer` > ' . (int)$id_customer . '
                AND cg.`id_group` = 1
                AND c.`deleted` = 0';

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    private function listProspectsByCoach($isAllow, $id_employee)
    {
        return ProspectAttribueClass::getListProspects($this->getDateBetween(), $isAllow, $id_employee);
    }

    private function getDateBetween()
    {
        return ModuleGraph::getDateBetween($this->context->employee);
    }

    private function changeGroupProspects(ProspectAttribueClass $pa, $groupNewCoach)
    {
        $groupOldCoach = $this->module->getGroupeEmployee($pa->id_employee);
        $prospects = ProspectClass::getProspectsByIdPa($pa->id_prospect_attribue);
        foreach ($prospects as $prospect) {
            $c = new Customer($prospect['id_customer']);
            $g = $c->getGroups();
            unset($g[array_search($groupOldCoach, $g)]);
            $g[] = (int)$groupNewCoach;
            $c->updateGroup($g);
            unset($c);
            unset($g);
        }
    }

    private function deleteProspectsAttribue()
    {
        $id_pa = (int)Tools::getValue('id_pa');
        if (ProspectAttribueClass::isExist($id_pa)) {
            $pa = new ProspectAttribueClass($id_pa);

            $this->changeGroupProspects($pa, '1');

            $this->deleteProspectsNonTraite($pa->id_prospect_attribue);
            $pa->delete();
            $this->confirmations = $this->module->l('Enregistrement éffacé.');
        }
    }

    private function getLastIdGroupDefaut()
    {
        $sql = 'SELECT MAX(`id_customer`) FROM `ps_customer_group`
                WHERE `id_group` = 1 ';

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    private function nbrProspectsDisponible($index_id)
    {
        $sql = 'SELECT COUNT(`id_customer`) FROM `ps_customer_group` 
                WHERE `id_customer` > ' . $index_id . '
                AND `id_group` = 1 ';
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }

    private function deleteProspectsNonTraite($id_prospect_attribue)
    {
        $sql = 'DELETE FROM `ps_prospect`
                WHERE `id_prospect_attribue` = ' . (int)$id_prospect_attribue . '
                AND `traite` = "Prospect"
                AND `injoignable` = "Non"
                AND `contacte` = "" ';
        $req = Db::getInstance()->execute($sql);

        return $req;
    }

    private function viewProspectsAttribue()
    {
        $id_pa = (int)Tools::getValue('id_pa');
        $this->smarty->assign(array(
            'listProspects' => ProspectClass::getProspectsByIdPa($id_pa)));
    }

    private function getProspectsIsole()
    {
        $prospects = ProspectClass::getProspectsIsole();
        return $prospects;
    }

    private function attribuProspectsIsoles(ProspectAttribueClass $attriProspect, $getGroupeEmployee)
    {
        $prospectsIsoles = ProspectClass::getProspectsIsole();

        for ($i = 0; $i < $attriProspect->nbr_prospect_attribue; $i++) {
            $prospect = new ProspectClass($prospectsIsoles[$i]['id_prospect']);
            $prospect->id_prospect_attribue = $attriProspect->id;

            $c = new Customer($prospect->id_customer);
            $g = $c->getGroups();
            unset($g[array_search('1', $g)]);
            $g[] = $getGroupeEmployee;
            $c->updateGroup($g);
            $prospect->update();
        }

    }


}