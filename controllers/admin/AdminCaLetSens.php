<?php

require_once(dirname(__FILE__) . '/../../classes/GridClass.php');
require_once(dirname(__FILE__) . '/../../classes/CaTools.php');
require_once(dirname(__FILE__) . '/../../classes/ProspectAttribueClass.php');
require_once(dirname(__FILE__) . '/../../../../tools/tcpdf/tcpdf.php');

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
        $this->module = 'cdmoduleca';
        $this->bootstrap = true;
        $this->className = 'AdminCaLetSens';
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdmoduleca/views/templates/admin/ca/';
        $this->employees_actif = 1;
        $this->commandeValid = 2;

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
            'idGroupEmployee' => $this->module->getGroupeEmployee($this->idFilterCoach),
            'idFilterCoach' => $this->idFilterCoach,
            'idFilterCodeAction' => $this->idFilterCodeAction,
            'commandeValid' => $this->commandeValid,
            'lang' => $this->module->lang,
            'CodeActionABO' => CaTools::getCodeActionByName('ABO'),
            'date' => $this->getDateBetween()
        );

        $this->context->smarty->assign(array(
            'LinkFile' => Tools::safeOutput($_SERVER['REQUEST_URI']),
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));

        $this->html .= $this->displayCalendar();
        $this->html .= $this->syntheseCoachs();
        $this->html .= $g->engine($engine_params);


        $nameFile = $this->nameFile();
        if (Tools::getValue('export_csv')) {
            $g->csvExport($engine_params, $nameFile);
        }
        if (Tools::getValue('export_pdf')) {
            $this->generatePDF($nameFile);
        }

        $this->content = $this->html;

        parent::initContent();
    }

    protected function syntheseCoachs()
    {
        $html = $this->smarty->fetch($this->path_tpl . 'synthesecoachsheader.tpl');
        $html .= $this->syntheseCoachsFilter();
        $html .= $this->syntheseCoachsContent();
        $html .= $this->syntheseCoachsTable();
        $html .= $this->smarty->fetch($this->path_tpl . 'synthesecoachsfooter.tpl');
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
            'caCoachsTotal' => CaTools::getCaCoachsTotal(0, $this->idFilterCodeAction, $this->getDateBetween()),
            'caCoach' => CaTools::getCaCoachsTotal($this->idFilterCoach, $this->idFilterCodeAction,
                $this->getDateBetween()),
            'caFidTotal' => CaTools::getCaDejaInscrit(0, $this->getDateBetween()),
            'caFidCoach' => CaTools::getCaDejaInscrit($this->idFilterCoach, $this->getDateBetween()),
            'caDeduitTotal' => CaTools::getCaDeduit(0, $this->getDateCaDeduit()),
            'caDeduitCoach' => CaTools::getCaDeduit($this->idFilterCoach, $this->getDateCaDeduit()),
            'caDeduitJours' => (int)Configuration::get('CDMODULECA_ORDERS_STATE_JOURS'),
            'caTotalNbrCommandes' => CaTools::getNumberCommande(0, $this->idFilterCodeAction, array(460, 443),
                $this->getDateBetween()),
            'caCoachNbrCommandes' => CaTools::getNumberCommande($this->idFilterCoach, $this->idFilterCodeAction,
                array(460, 443), $this->getDateBetween()),
            'caTotal' => CaTools::getCaCoachsTotal(0, 0, $this->getDateBetween()),
            'caTotalCoach' => CaTools::getCaCoachsTotal($this->idFilterCoach, 0, $this->getDateBetween()),
            'caTotalAbo' => CaTools::getNbrGrVentes(0, 'ABO', array(444, 462), false, true, $this->getDateBetween(),
                $this->module->lang),
            'coach' => new Employee($this->idFilterCoach),
            'filterCodeAction' => $this->getCodeActionByID(),
            'nbrJourOuvre' => CaTools::get_nb_open_days($this->getDateBetween()),
            'primeFichierTotal' => CaTools::primeFichier(0, $this->getDateBetween()),
            'primeFichierCoach' => CaTools::primeFichier($this->idFilterCoach, $this->getDateBetween()),
        ));
    }

    private function syntheseCoachsTable()
    {
        $employees = CaTools::getEmployees(1, $this->context->employee->id);

        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if ($this->idFilterCoach == 0) {
                $employees = CaTools::getEmployees($this->employees_actif);
            } else {
                $employees = CaTools::getEmployees(null,$this->idFilterCoach);
            }

        }

        $datasEmployees = array();
        foreach ($employees as $employee) {

            $datasEmployees[$employee['id_employee']]['lastname'] = $employee['lastname'];
            $datasEmployees[$employee['id_employee']]['firstname'] = $employee['firstname'];

            $datasEmployees[$employee['id_employee']]['caRembourse'] =
                CaTools::getCaCoachsRembourse($employee['id_employee'], 0, $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['caAvoir'] =
                CaTools::getCaCoachsAvoir($employee['id_employee'], $this->getDateBetween());

            $caTotal = CaTools::getCaCoachsTotal($employee['id_employee'], 99, $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['caTotal'] = ($caTotal) ? $caTotal : '';

            $pourCaRembourse = ($caTotal) ? round((($datasEmployees[$employee['id_employee']]['caRembourse'] * 100)
                / $caTotal), 2) : '';

            $datasEmployees[$employee['id_employee']]['pourCaRembourse'] = ($pourCaRembourse) ? $pourCaRembourse . ' %' : '';

            $datasEmployees[$employee['id_employee']]['ajustement'] =
                CaTools::getAjustement($employee['id_employee'], $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['caAjuste'] = ($datasEmployees[$employee['id_employee']]['caTotal'])
                ? ($datasEmployees[$employee['id_employee']]['caTotal']
                    + $datasEmployees[$employee['id_employee']]['ajustement'])
                - $datasEmployees[$employee['id_employee']]['caRembourse']
                - $datasEmployees[$employee['id_employee']]['caAvoir'] : '';

            $datasEmployees[$employee['id_employee']]['caRembAvoir'] = ($datasEmployees[$employee['id_employee']]['caTotal'])
                ? $datasEmployees[$employee['id_employee']]['caRembourse']
                + $datasEmployees[$employee['id_employee']]['caAvoir'] : '';

            $pourCaRembAvoir = ($caTotal) ? round((($datasEmployees[$employee['id_employee']]['caRembAvoir'] * 100)
                / $caTotal), 2) : '';

            $datasEmployees[$employee['id_employee']]['pourCaRembAvoir'] = ($pourCaRembAvoir) ? $pourCaRembAvoir . ' %' : '';

            $datasEmployees[$employee['id_employee']]['caDejaInscrit'] =
                CaTools::getCaDejaInscrit($employee['id_employee'], $this->getDateBetween());

            $caProsp = CaTools::caProsp($datasEmployees[$employee['id_employee']])
                - $datasEmployees[$employee['id_employee']]['caRembAvoir'];

            $datasEmployees[$employee['id_employee']]['CaProsp'] = ($caProsp) ? $caProsp : '';

            $datasEmployees[$employee['id_employee']]['PourcCaProspect'] =
                CaTools::PourcCaProspect($datasEmployees[$employee['id_employee']]);

            $datasEmployees[$employee['id_employee']]['PourcCaFID'] =
                CaTools::PourcCaFID($datasEmployees[$employee['id_employee']]);

            $datasEmployees[$employee['id_employee']]['caFidTotal'] =
                CaTools::getCaDejaInscrit($employee['id_employee'], $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['NbreVentesTotal'] =
                CaTools::getNumberCommande($employee['id_employee'], null, array(460, 443), $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['NbreDeProspects'] =
                ProspectAttribueClass::getNbrProspectsAttriByCoach($employee['id_employee'], $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['CaContact'] = ($datasEmployees[$employee['id_employee']]['NbreDeProspects'])
                ? round((($datasEmployees[$employee['id_employee']]['caAjuste']
                        - $datasEmployees[$employee['id_employee']]['caFidTotal'])
                    / $datasEmployees[$employee['id_employee']]['NbreDeProspects']), 2) : '';

            $datasEmployees[$employee['id_employee']]['panierMoyen'] =
                CaTools::getPanierMoyen($datasEmployees[$employee['id_employee']]);

            $datasEmployees[$employee['id_employee']]['nbrVenteAbo'] =
                CaTools::getNbrVentes($employee['id_employee'], 'ABO', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['nbrVenteProsp'] =
                CaTools::getNbrVentes($employee['id_employee'], 'Prosp', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['nbrVenteFid'] =
                CaTools::getNbrVentes($employee['id_employee'], 'FID', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['tauxTransfo'] = ($datasEmployees[$employee['id_employee']]['NbreDeProspects'] != 0) ?
                (round(((($datasEmployees[$employee['id_employee']]['NbreVentesTotal']
                            - $datasEmployees[$employee['id_employee']]['nbrVenteFid']) * 100)
                    / $datasEmployees[$employee['id_employee']]['NbreDeProspects']), 2)) . ' %' : '';

            $datasEmployees[$employee['id_employee']]['nbrVentePar'] =
                CaTools::getNbrVentes($employee['id_employee'], 'PAR', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['nbrVenteReact'] =
                CaTools::getNbrVentes($employee['id_employee'], 'REACT+4M', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['nbrVenteCont'] =
                CaTools::getNbrVentes($employee['id_employee'], 'CONT ENTR', $this->getDateBetween());

            $datasEmployees[$employee['id_employee']]['nbrVenteGrAbo'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462), false, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['totalVenteGrAbo'] = CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462), true, true,
                $this->getDateBetween(), $this->module->lang);

            $n = $datasEmployees[$employee['id_employee']]['totalVenteGrAbo'];

            $primeVenteGrAbo = ($n) ? ($n / 100) * 10 : ''; // Calcul de la prime 10 % sur la vente des abos
            $datasEmployees[$employee['id_employee']]['primeVenteGrAbo'] = $primeVenteGrAbo;

            $datasEmployees[$employee['id_employee']]['nbrVenteGrDesaAbo'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(440, 453), false, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['pourcenDesabo'] =
                ($datasEmployees[$employee['id_employee']]['nbrVenteGrAbo'])
                    ? round((($datasEmployees[$employee['id_employee']]['nbrVenteGrDesaAbo'] * 100)
                        / $datasEmployees[$employee['id_employee']]['nbrVenteGrAbo']), 2) . ' %' : '';

            $datasEmployees[$employee['id_employee']]['nbrVenteGrFid'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'FID', null, false, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['totalVenteGrFid'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'FID', null, true, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrProsp'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'PROSP', null, false, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['totalVenteGrProsp'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'PROSP', null, true, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrPar'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'PAR', null, false, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['totalVenteGrPar'] =
                CaTools::getNbrGrVentes($employee['id_employee'], 'PAR', null, true, false,
                    $this->getDateBetween(), $this->module->lang);

            $datasEmployees[$employee['id_employee']]['pourVenteGrPar'] =
                ($datasEmployees[$employee['id_employee']]['totalVenteGrPar'])
                    ? round(($datasEmployees[$employee['id_employee']]['totalVenteGrPar'] * 100)
                        / $datasEmployees[$employee['id_employee']]['caAjuste'], 2) . ' %' : '';

            $datasEmployees[$employee['id_employee']]['primeFichierCoach'] =
                CaTools::primeFichier($employee['id_employee'], $this->getDateBetween());
        }


        $this->smarty->assign(array(
            'datasEmployees' => $datasEmployees,
            'dateRequete' => $this->getDateBetween()
        ));

        return $this->smarty->fetch($this->path_tpl . 'synthesecoachstable.tpl');
    }

    private function syntheseCoachsFilter()
    {
        $linkFilterCoachs = AdminController::$currentIndex . '&module=' . $this->module->name
            . '&token=' . Tools::getValue('token');
        $this->smarty->assign(array(
            'linkFilter' => $linkFilterCoachs,
        ));
        $this->syntheseCoachsFilterCoach();
        $this->syntheseCoachsFilterCodeAction();

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

    private function syntheseCoachsFilterCodeAction()
    {
        $listCodesAction = CaTools::getAllGroupeCodesAction();
        $listCodesAction[] = array(
            'id_code_action' => '0',
            'name' => 'Tous les codes'
        );

        $listCodesAction[] = array(
            'id_code_action' => '99',
            'name' => 'Tous les codes sauf ABO'
        );
        $this->smarty->assign(array(
            'codesAction' => $listCodesAction,
            'filterCodeAction' => $this->idFilterCodeAction
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

    /**
     * Enregistrement de la configuration du filtre commande valide dans un cookie
     */
    private function setFilterCommandeValid()
    {
        $this->commandeValid = 2;
        if (Tools::isSubmit('submitFilterCommande')) {
            $this->context->cookie->cdmoculeca_filter_commande = Tools::getValue('filterCommande');
        }
        $this->commandeValid = $this->context->cookie->cdmoculeca_filter_commande;

    }

    /**
     * Enregistrement de la configuration du filtre code action dans un cookie
     * @return string
     */
    private function setIdFilterCodeAction()
    {
        if (Tools::isSubmit('submitFilterCodeAction')) {
            $this->context->cookie->cdmoduleca_id_filter_code_action = Tools::getValue('filterCodeAction');
        }
        $this->idFilterCodeAction = ($this->context->cookie->cdmoduleca_id_filter_code_action)
            ? $this->context->cookie->cdmoduleca_id_filter_code_action : '0';

        return $this->idFilterCodeAction;
    }

    /**
     * Enregistre, modifie, oou efface une ligne de la table ajout_somme
     */
    private function AjoutSomme()
    {
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('as_submit')) {
                $data = array(
                    'id_employee' => (int)Tools::getValue('as_id_employee'),
                    'somme' => Tools::getValue('as_somme'),
                    'commentaire' => pSQL(Tools::getValue('as_commentaire')),
                    'date_add' => Tools::getValue('as_date')
                );
                if (!Validate::isInt($data['id_employee'])) {
                    $this->errors[] = 'L\'id de l\'employee n\'est pas valide';
                }
                if (!Validate::isFloat(str_replace(',', '.', $data['somme']))) {
                    $this->errors[] = 'La somme n\'est pas valide';
                }
                if (!Validate::isString($data['commentaire'])) {
                    $this->errors[] = 'Erreur du champ commentaire';
                }
                if (!Validate::isDate($data['date_add'])) {
                    $this->errors[] = 'Erreur du champ date';
                }

                if (!$this->errors) {
                    // Modifie
                    if (Tools::getValue('as_id')) {
                        $data['id_ajout_somme'] = (int)Tools::getValue('as_id');
                        if (!Db::getInstance()->update('ajout_somme', $data, 'id_ajout_somme = '
                            . (int)Tools::getValue('as_id'))
                        ) {
                            $this->errors[] = $this->l('Erreur lors de la mise à jour');
                        }
                    } else {
                        // Insert
                        if (!Db::getInstance()->insert('ajout_somme', $data)) {
                            $this->errors[] = $this->l('Erreur lors de l\'ajout.');
                        }
                    }
                    if (!$this->errors) {
                        $this->confirmation = $this->l('Enregistrement éffectué.');
                        unset($_POST['as_id_employee']);
                        unset($_POST['as_somme']);
                        unset($_POST['as_commentaire']);
                        unset($_POST['as_date']);
                        unset($_POST['as_id']);
                    }
                }
                // Efface
            } elseif (Tools::isSubmit('del_as')) {
                $id = (int)Tools::getValue('id_as');
                if (!Db::getInstance()->delete('ajout_somme', 'id_ajout_somme = ' . $id)) {
                    $this->errors[] = $this->l('Erreur lors de la suppression');
                } else {
                    $this->confirmation = $this->l('Ajout manuel supprimé.');
                }
            } elseif (Tools::isSubmit('mod_as')) {
                $as = CaTools::getAjoutSommeById((int)Tools::getValue('id_as'));
                $_POST['as_id_employee'] = $as['id_employee'];
                $_POST['as_somme'] = $as['somme'];
                $_POST['as_commentaire'] = $as['commentaire'];
                $_POST['as_date'] = $as['date_add'];
                $_POST['as_id'] = $as['id_ajout_somme'];
            }
        }

        $ajoutSommes = CaTools::getAjoutSomme($this->idFilterCoach, $this->getDateBetween());

        $this->smarty->assign(array(
            'ajoutSommes' => $ajoutSommes
        ));
        $this->smarty->assign(array(
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));
    }

    /**
     * Enregistre, modifie, ou efface une ligne de la table objectif_coach
     */
    private function AjoutObjectif()
    {
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('oc_submit')) {
                $data = array(
                    'id_employee' => (int)Tools::getValue('oc_id_employee'),
                    'somme' => Tools::getValue('oc_somme'),
                    'commentaire' => pSQL(Tools::getValue('oc_commentaire')),
                    'heure_absence' => Tools::getValue('oc_heure'),
                    'jour_absence' => Tools::getValue('oc_jour'),
                    'jour_ouvre' => Tools::getValue('oc_jour_ouvre'),
                    'date_start' => Tools::getValue('oc_date_start'),
                    'date_end' => date('Y-m-d 23:59:59', strtotime(Tools::getValue('oc_date_end')))
                );
                if (!Validate::isInt($data['id_employee'])) {
                    $this->errors[] = 'L\'id de l\'employee n\'est pas valide';
                }
                if (!empty($data['somme']) && !Validate::isFloat(str_replace(',', '.', $data['somme']))) {
                    $this->errors[] = 'La somme n\'est pas valide';
                }
                if (!Validate::isString($data['commentaire'])) {
                    $this->errors[] = 'Erreur du champ commentaire';
                }
                if (!empty($data['heure_absence']) && !Validate::isFloat(str_replace(',', '.', $data['heure_absence']))) {
                    $this->errors[] = 'Erreur du champ heure d\'absence';
                }
                if (!empty($data['jour_absence']) && !Validate::isInt($data['jour_absence'])) {
                    $this->errors[] = 'Erreur du champ jour d\'absence';
                }
                if (!empty($data['jour_ouvre']) && !Validate::isInt($data['jour_ouvre'])) {
                    $this->errors[] = 'Erreur du champ jour ouvré';
                }
                if (!Validate::isDate($data['date_start'])) {
                    $this->errors[] = 'Erreur du champ date début';
                }
                if (!Validate::isDate($data['date_end'])) {
                    $this->errors[] = 'Erreur du champ date fin';
                }


                if (empty($this->errors)) {
                    // Modifie
                    if (Tools::getValue('oc_id')) {
                        $data['id_objectif_coach'] = (int)Tools::getValue('oc_id');
                        if (!Db::getInstance()->update('objectif_coach', $data, 'id_objectif_coach = '
                            . (int)Tools::getValue('oc_id'))
                        ) {
                            $this->errors[] = $this->l('Erreur lors de la mise à jour');
                        }
                    } else {
                        // Insert
                        if (!Db::getInstance()->insert('objectif_coach', $data)) {
                            $this->errors[] = $this->l('Erreur lors de l\'ajout.');
                        }
                    }
                    if (empty($this->errors)) {
                        $this->confirmation = $this->l('Enregistrement éffectué.');
                        unset($_POST['oc_id_employee']);
                        unset($_POST['oc_somme']);
                        unset($_POST['oc_commentaire']);
                        unset($_POST['oc_heure']);
                        unset($_POST['oc_jour']);
                        unset($_POST['oc_jour_ouvre']);
                        unset($_POST['oc_date_start']);
                        unset($_POST['oc_date_end']);
                        unset($_POST['oc_id']);
                    }
                }
                // Efface
            } elseif (Tools::isSubmit('del_oc')) {
                $id = (int)Tools::getValue('id_oc');
                if (!Db::getInstance()->delete('objectif_coach', 'id_objectif_coach = ' . $id)) {
                    $this->errors[] = $this->l('Erreur lors de la suppression');
                } else {
                    $this->confirmation = $this->l('Objectif supprimé.');
                }
            } elseif (Tools::isSubmit('mod_oc')) {
                $oc = CaTools::getObjectifById((int)Tools::getValue('id_oc'));
                $_POST['oc_id_employee'] = $oc['id_employee'];
                $_POST['oc_somme'] = $oc['somme'];
                $_POST['oc_commentaire'] = $oc['commentaire'];
                $_POST['oc_heure'] = $oc['heure_absence'];
                $_POST['oc_jour'] = $oc['jour_absence'];
                $_POST['oc_jour'] = $oc['jour_ouvre'];
                $_POST['oc_date_start'] = $oc['date_start'];
                $_POST['oc_date_end'] = $oc['date_end'];
                $_POST['oc_id'] = $oc['id_objectif_coach'];
            }
        }

        $objectifCoachs = CaTools::getObjectifCoachs($this->idFilterCoach, $this->getDateBetween());
        $objectifs = CaTools::isObjectifAtteint($objectifCoachs);
        $this->smarty->assign(array(
            'objectifCoachs' => $objectifs
        ));
        $this->smarty->assign(array(
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));
    }

    public function postProcess()
    {
        $this->processDateRange();
        $this->setIdFilterCoach();
        $this->setIdFilterCodeAction();
        $this->setFilterCommandeValid();
        $this->AjoutSomme();
        $this->AjoutObjectif();

        return parent::postProcess();
    }

    private function getDateBetween()
    {
        return ModuleGraph::getDateBetween($this->context->employee);
    }

    private function getDateCaDeduit()
    {
        $d = $this->getDateBetween();
        $days = Configuration::get('CDMODULECA_ORDERS_STATE_JOURS');
        $d_start = "'" . date('Y-m-d H:i:s', strtotime(substr($d, 2, 19) . ' - ' . $days . ' days')) . "'";
        $d_end = "'" . date('Y-m-d H:i:s', strtotime(substr($d, 28, 19) . ' - ' . $days . ' days')) . "'";

        return $d_start . ' AND ' . $d_end;
    }

    public function displayCalendar()
    {
        return AdminCaLetSensController::displayCalendarForm(array(
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

        $action = Context::getContext()->link->getAdminLink('AdminCaLetSens');
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

        $tpl = $this->smarty->fetch($this->path_tpl . 'calendar/form_date_range_picker.tpl');
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

    private function generatePDF($nameFile)
    {
        $pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('L&Sens');
        $pdf->SetTitle('Module CA');
        $pdf->SetSubject('Module CA');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);


        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $l = '';
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/fr.php')) {
            require_once(dirname(__FILE__) . '/lang/fr.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('dejavusans', 'BI', 20);
        $pdf->SetMargins(7, 10, 7, true);
        // add a page
        $pdf->AddPage();

        // set some text to print
        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/pdf/content.tpl');
        $pdf->writeHTML($html_content);

        $pdf->AddPage();
        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/pdf/main_table_coachs.tpl');
        $pdf->writeHTML($html_content);

        $pdf->AddPage();
        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/pdf/main_table_groupes.tpl');
        $pdf->writeHTML($html_content);
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output($nameFile . '.pdf', 'D');
    }

    private function getCodeActionByID()
    {
        $ca = array();
        if ($this->idFilterCodeAction == 99) {
            $ca['name'] = 'Tous les codes sauf ABO';
        } elseif ($this->idFilterCodeAction == 0) {
            $ca['name'] = 'Tous les codes';
        } else {
            $ca = CaTools::getCodeAction($this->idFilterCodeAction);
        }

        return $ca;
    }

    private function nameFile()
    {
        $name = substr($this->getDateBetween(), 2, 10) . '_' . substr($this->getDateBetween(), 28, 10) . '_';
        if ($this->idFilterCoach == 0) {
            $name .= 'tous_les_coachs';
        } else {
            $e = new Employee($this->idFilterCoach);
            $name .= $e->lastname . '_' . $e->firstname;
        }

        setlocale(LC_ALL, "en_US.utf8");
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        setlocale(LC_ALL, "fr_FR.utf8");

        return utf8_decode($name);
    }

}

class MYPDF extends TCPDF
{

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
