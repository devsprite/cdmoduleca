<?php

require_once(dirname(__FILE__) . '/../../classes/GridClass.php');
require_once(dirname(__FILE__) . '/../../classes/CaTools.php');

class AdminCaLetSensController extends ModuleAdminController
{
    public $html = '';
    public $path_tpl;
    public $smarty;
    public $confirmation;
    public $errors = array();
    public $idFilterCoach;
    public $idFilterCodeAction;
    public $employees_actif;
    public $commandeValid;

    public function __construct()
    {
        //Tools::redirectAdmin('index.php?controller=AdminStats&module=cdmoduleca&token='.Tools::getAdminTokenLite('AdminStats'));
        $this->module = 'cdmoduleca';
        $this->bootstrap = true;
        $this->className = 'AdminCaLetSens';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/ca/';
        $this->employees_actif = 1;
        $this->commandeValid = 1;
        parent::__construct();
    }



    public function initContent()
    {
        $this->context->controller->addCSS(_PS_MODULE_DIR_ . 'cdmoduleca/views/css/statscdmoduleca.css');
        $engine_params = array(
            'id' => 'id_order',
            'title' => $this->module->displayName,
            'columns' => $this->module->columns,
            'defaultSortColumn' => $this->module->default_sort_column,
            'defaultSortDirection' => $this->module->default_sort_direction,
            'emptyMessage' => $this->module->empty_message,
            'pagingMessage' => $this->module->paging_message,
            'limit' => $this->module->limit,
        );

        $g = new GridClass();
        $g->data = array(
            'idGroupEmployee' => $this->module->getGroupeEmployee($this->module->idFilterCoach),
            'idFilterCoach' => $this->module->idFilterCoach,
            'idFilterCodeAction' => $this->module->idFilterCodeAction,
            'commandeValid' => $this->module->commandeValid,
            'lang' => $this->module->lang,
            'CodeActionABO' => $this->module->getCodeActionByName('ABO'),
            'date' => $this->module->getDate()
        );

        $this->context->smarty->assign(array(
            'CSVLink' => Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1'),
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));


        if (Tools::getValue('export')) {
            $g->csvExport($engine_params);
        }

        $this->html .= $this->syntheseCoachs();
        $this->html .= $g->engine($engine_params);
        $this->content = $this->html;
        return parent::initContent();
    }

    protected function syntheseCoachs()
    {
        $html = $this->smarty->fetch($this->path_tpl . 'synthesecoachsheader.tpl');
        $html .= $this->syntheseCoachsFilter();
        $html .= $this->syntheseCoachsContent();
//        $html .= $this->syntheseCoachsTable();
//        $html .= $this->display($this->path_tpl, 'ca/synthesecoachsfooter.tpl');
        return $html;
    }

    private function syntheseCoachsContent()
    {
        $this->syntheseCoachsContentGetData();
        return $this->smarty->fetch($this->path_tpl . 'synthesecoachscontent.tpl');
    }

    private function syntheseCoachsContentGetData()
    {
        $this->smarty->assign(array(
            'caCoachsTotal' => CaTools::getCaCoachsTotal(0, $this->idFilterCodeAction, $this->getDate()),
            'caCoach' => CaTools::getCaCoachsTotal($this->idFilterCoach, $this->idFilterCodeAction, $this->getDate()),
//            'caFidTotal' => $this->getCaDejaInscrit(0),
//            'caFidCoach' => $this->getCaDejaInscrit($this->idFilterCoach),
//            'caDeduitTotal' => $this->getCaDeduit(),
//            'caDeduitCoach' => $this->getCaDeduit($this->idFilterCoach),
//            'caDeduitJours' => (int)Configuration::get('CDMODULECA_ORDERS_STATE_JOURS'),
//            'caTotalNbrCommandes' => $this->getNumberCommande(0, $this->idFilterCodeAction, array(460, 443)),
//            'caCoachNbrCommandes' => $this->getNumberCommande($this->idFilterCoach, $this->idFilterCodeAction, array(460, 443)),
//
//            'caTotal' => $this->getCaCoachsTotal(0, 0),
//            'caTotalCoach' => $this->getCaCoachsTotal($this->idFilterCoach, 0),
//            'coach' => new Employee($this->idFilterCoach),
//            'filterCodeAction' => $this->getCodeAction($this->idFilterCodeAction),
        ));
    }

    private function syntheseCoachsFilter()
    {
        $linkFilterCoachs = AdminController::$currentIndex . '&module=' . $this->module->name
            . '&token=' . Tools::getValue('token');
        $this->smarty->assign(array(
            'linkFilter' => $linkFilterCoachs,
        ));

        $this->syntheseCoachsFilterCoach();
//        $this->syntheseCoachsFilterCodeAction();
        return $this->smarty->fetch($this->path_tpl . 'synthesecoachsfilter.tpl');

    }

    private function syntheseCoachsFilterCoach()
    {
        $idProfil = $this->context->employee->id_profile;
        $commandeActive = array(
            array('key' => 'Non', 'value' => '0'),
            array('key' => 'Oui', 'value' => '1'),
            array('key' => 'Tout', 'value' => '2'));

        if ($this->module->viewAllCoachs[$idProfil]) {
            $listCoaches = CaTools::getEmployees($this->employees_actif);
            $listCoaches[] = array(
                'id_employee' => '0',
                'lastname' => 'Tous les coachs',
                'firstname' => '---');

            $this->smarty->assign(array(
                'coachs' => $listCoaches,
                'filterCoachActif' => $this->employees_actif,
            ));
        }
        $this->smarty->assign(array(
            'filterActif' => (int)$this->idFilterCoach,
            'filterCommandeActive' => $this->commandeValid,
            'commandeActive' => $commandeActive,
        ));
    }

    /**
     * Enregistrement de la configuration du filtre coach dans un cookie
     */
    private function setIdFilterCoach()
    {
        $this->idFilterCoach = (int)$this->context->employee->id;
        $this->employees_actif = 1;
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('submitFilterCoachs')) {
                $this->context->cookie->cdmoculeca_id_filter_coach = Tools::getValue('filterCoach');
                $this->context->cookie->cdmoculeca_id_filter_coach_actif = Tools::getValue('filterCoachActif');
            }
            $this->idFilterCoach = $this->context->cookie->cdmoculeca_id_filter_coach;
            $this->employees_actif = $this->context->cookie->cdmoculeca_id_filter_coach_actif;
        }
    }

    public function postProcess()
    {
        $this->setIdFilterCoach();
        return parent::postProcess();
    }

    public function getDate()
    {
        return ModuleGraph::getDateBetween($this->context->employee);
    }
}
