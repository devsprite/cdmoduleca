<?php

require_once(dirname(__FILE__) . '/../../classes/ProspectClass.php');
require_once(dirname(__FILE__) . '/../../classes/ProspectAttribueClass.php');

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
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/';
        parent::__construct();
    }

    public function init()
    {
        parent::init();
        $this->employesActif = $this->context->cookie->cdmoduleca_admin_prospect_employe_actif;

        $this->displayForm();
        $this->displayProspects();

        $this->smarty->assign(array(
            'employeActif' => $this->employesActif,
            'confirmation' => $this->confirmations,
            'errors' => $this->errors
        ));

        $this->html .= $this->smarty->fetch($this->path_tpl . 'prospectsForm.tpl');
        $this->html .= $this->smarty->fetch($this->path_tpl . 'prospectsList.tpl');
        $this->content = $this->html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitEmployeActif')) {
            $this->setEmployeActif(Tools::getValue('employeActif'));
        } elseif (Tools::isSubmit('submitEmployes')) {
            $this->setEmployesAttribue();
        }
        return parent::postProcess();
    }

    private function setEmployesAttribue()
    {
        $isOk = true;
        $date_debut = Tools::getValue('p_date_start');
        $date_fin = Tools::getValue('p_date_end');
        if ($date_debut <= $date_fin) {
            foreach ($_POST as $key => $nbrProspect) {
                if (substr($key, 0, 3) == 'em_' && !empty($nbrProspect) && $isOk) {
                    $id_employe = str_replace('em_', '', $key);
                    $attriProspect = new ProspectAttribueClass();
                    $attriProspect->date_debut = $date_debut;
                    $attriProspect->date_fin = $date_fin;
                    $attriProspect->id_employee = $id_employe;
                    $attriProspect->nbr_prospect_attribue = $nbrProspect;
                    $isOk = $attriProspect->add();
                    $this->attribuProspects($attriProspect, $this->module->getGroupeEmployee($id_employe));
                }
            }
        }
        if ($isOk) {
            $this->confirmations = $this->module->l('Enregistrement éffectué');
        } else {
            $this->errors = $this->module->l('Erreur lors de l\'enregistrement');
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

    private function displayForm()
    {
        $this->generateForm();
    }

    private function generateForm()
    {
        $linkForm = AdminController::$currentIndex . '&token=' . Tools::getValue('token');
        $employes = $this->getEmployees();
        $this->smarty->assign(array(
            'employes' => $employes,
            'linkForm' => $linkForm
        ));
    }

    private function displayProspects()
    {
        $prospectsGroupe_1 = ProspectClass::getAllProspectsGroup(1);
        $this->smarty->assign(array('prosGr1' => $prospectsGroupe_1));

    }

    private function getEmployees()
    {
        $actif = (empty($this->employesActif)) ? '' : ' WHERE e.`active` = 1';
        $sql = '
            SELECT e.`id_employee`, e.`lastname`, e.`firstname`, pa.`nbr_prospect_attribue`,pa.`date_debut`,
             pa.`date_fin`
            FROM `ps_employee` AS e 
            LEFT JOIN `ps_prospect_attribue` AS pa ON e.`id_employee` = pa.`id_employee`
            LEFT JOIN `ps_prospect` AS p ON pa.`id_prospect_attribue` = p.`id_prospect_attribue`
            ';
        $sql .= $actif;
        $sql .= ' GROUP BY e.`id_employee`';

        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    private function attribuProspects(ProspectAttribueClass $ap, $id_group)
    {
        $prospects = ProspectClass::getAllProspectsGroup(1, $ap->nbr_prospect_attribue, 12, true);
        foreach ($prospects as $prospect) {
            $p = new Customer($prospect['id_customer']);
            var_dump($p->id);
            $p->updateGroup(array($id_group));
        }
    }
}