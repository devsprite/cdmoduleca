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
        $this->module = 'cdmoduleca';
        $this->bootstrap = true;
        $this->className = 'AdminCaLetSens';
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
            'idGroupEmployee' => $this->module->getGroupeEmployee($this->idFilterCoach),
            'idFilterCoach' => $this->idFilterCoach,
            'idFilterCodeAction' => $this->idFilterCodeAction,
            'commandeValid' => $this->commandeValid,
            'lang' => $this->module->lang,
            'CodeActionABO' => CaTools::getCodeActionByName('ABO'),
            'date' => $this->getDateBetween()
        );

        $this->context->smarty->assign(array(
            'CSVLink' => Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1'),
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));

        if (Tools::getValue('export')) {
            $g->csvExport($engine_params);
        }

        $this->html = '
			<a class="btn btn-default export-csv" href="' . Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1') . '">
				<i class="icon-cloud-upload"></i> ' . $this->l('CSV Export') . '
			</a>';

        $this->html .= $this->displayCalendar();
        $this->html .= $this->syntheseCoachs();
        $this->html .= $g->engine($engine_params);
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
            'coach' => new Employee($this->idFilterCoach),
            'filterCodeAction' => CaTools::getCodeAction($this->idFilterCodeAction),
        ));
    }

    private function syntheseCoachsTable()
    {
        $employees = CaTools::getEmployees(1, $this->context->employee->id);
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            $employees = CaTools::getEmployees($this->employees_actif);
        }

        $datasEmployees = array();
        foreach ($employees as $employee) {
            $id_e = CaTools::getCaCoachsTotal($employee['id_employee'], 0, $this->getDateBetween());
            if (!empty($id_e)) {

                $datasEmployees[$employee['id_employee']]['lastname'] = $employee['lastname'];
                $datasEmployees[$employee['id_employee']]['firstname'] = $employee['firstname'];

                $datasEmployees[$employee['id_employee']]['caTotal'] =
                    CaTools::getCaCoachsTotal($employee['id_employee'], 0, $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['caDejaInscrit'] =
                    CaTools::getCaDejaInscrit($employee['id_employee'], $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['CaProsp'] =
                    CaTools::caProsp($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['PourcCaProspect'] =
                    CaTools::PourcCaProspect($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['PourcCaFID'] =
                    CaTools::PourcCaFID($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['NbrCommandes'] =
                    CaTools::getNumberCommande($employee['id_employee'], null, array(460, 443), $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['panierMoyen'] =
                    CaTools::getPanierMoyen($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['nbrVenteAbo'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'ABO', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVenteProsp'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'Prosp', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVenteFid'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'FID', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVentePar'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'PAR', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVenteReact'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'REACT+4M', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVenteCont'] =
                    CaTools::getNbrVentes($employee['id_employee'], 'CONT ENTR', $this->getDateBetween());

                $datasEmployees[$employee['id_employee']]['nbrVenteGrAbo'] =
                    CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462), false, false,
                        $this->getDateBetween(), $this->module->lang);

                $n = CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462), true, false,
                    $this->getDateBetween(), $this->module->lang);
                $totalVenteGrAbo = ($n) ? ($n / 100) * 10 : ''; // Calcul de la prime 10 % sur la vente des abos
                $datasEmployees[$employee['id_employee']]['totalVenteGrAbo'] = $totalVenteGrAbo;

                $datasEmployees[$employee['id_employee']]['nbrVenteGrDesaAbo'] =
                    CaTools::getNbrGrVentes($employee['id_employee'], 'ABO', array(440, 453), false, false,
                        $this->getDateBetween(), $this->module->lang);

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
            }

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
        $this->commandeValid = 1;
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
     * Enregistre, modifie, oou efface une ligne de la table objectif_coach
     */
    private function AjoutObjectif()
    {
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('oc_submit')) {
                $data = array(
                    'id_employee' => (int)Tools::getValue('oc_id_employee'),
                    'somme' => Tools::getValue('oc_somme'),
                    'commentaire' => pSQL(Tools::getValue('oc_commentaire')),
                    'date_start' => Tools::getValue('oc_date_start'),
                    'date_end' => date('Y-m-d 23:59:59', strtotime(Tools::getValue('oc_date_end')))
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
                $_POST['oc_date_start'] = $oc['date_start'];
                $_POST['oc_date_end'] = $oc['date_end'];
                $_POST['oc_id'] = $oc['id_objectif_coach'];
            }
        }

        $objectifCoachs = CaTools::getObjectifCoachs($this->idFilterCoach, $this->getDateBetween());
        $objectifs = CaTools::isObjectifAteint($objectifCoachs);
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

}
