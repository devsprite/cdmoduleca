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

require_once(dirname(__FILE__) . '/../../classes/GridClass.php');
require_once(dirname(__FILE__) . '/../../classes/AjoutSommeClass.php');
require_once(dirname(__FILE__) . '/../../classes/CaTools.php');
require_once(dirname(__FILE__) . '/../../classes/HistoAjoutSommeClass.php');
require_once(dirname(__FILE__) . '/../../classes/HistoObjectifCoachClass.php');
require_once(dirname(__FILE__) . '/../../classes/HistoStatsMainClass.php');
require_once(dirname(__FILE__) . '/../../classes/HistoStatsTableClass.php');
require_once(dirname(__FILE__) . '/../../classes/ProspectAttribueClass.php');
require_once(dirname(__FILE__) . '/../../../../tools/tcpdf/tcpdf.php');

/**
 * Class AdminCaLetSensController
 * Controller de la page stat L&Sens
 */
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
    public $histoMain = false;
    public $histoAjoutSomme;
    public $histoObjectif;
    public $histoTable;

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
        $this->context->controller->addJS(_PS_MODULE_DIR_ . 'cdmoduleca/views/js/statscdmoduleca.js');
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
            'allow' => $this->module->viewAllCoachs[$this->context->employee->id_profile]
        ));

        $this->html .= $this->displayCalendar(); // choix de la date
        $this->html .= $this->syntheseCoachs();
        if (!Tools::isSubmit('id_histo')) {
            $this->html .= $g->engine($engine_params); // liste des commandes (utilise le getData de cdmoduleca.php) du bas de la page
        }


        $nameFile = $this->nameFile();


        if (Tools::isSubmit('export_csv')) {
            $g->csvExport($engine_params, $nameFile);
        }
        if (Tools::isSubmit('export_pdf')) {
            $this->generatePDF($nameFile);
        }

        if (Tools::isSubmit('histo')) {
            $this->historique();
        }


        $this->content = $this->html;

        parent::initContent();
    }

    protected function syntheseCoachs()
    {
        $this->syntheseCoachsTable();
        $html = $this->smarty->fetch($this->path_tpl . 'synthesecoachsheader.tpl');
        $html .= $this->syntheseCoachsFilter();
        $html .= $this->syntheseCoachsContent();
        $html .= $this->smarty->fetch($this->path_tpl . 'synthesecoachstable.tpl');
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
            'coach' => new Employee($this->idFilterCoach),
            'filterCodeAction' => $this->getCodeActionByID(),
        ));
    }

    private function syntheseCoachsTable()
    {
        $employees = CaTools::getEmployees(1, $this->context->employee->id);

        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if ($this->idFilterCoach == 0) {
                $employees = CaTools::getEmployees($this->employees_actif);
            } else {
                $employees = CaTools::getEmployees(null, $this->idFilterCoach);
            }
        }

        $datasEmployees = array();
        foreach ($employees as $employee) {

            $id_employe = $employee['id_employee'];

            $datasEmployees[$employee['id_employee']]['id_employee'] = $id_employe;

            $datasEmployees[$employee['id_employee']]['lastname'] = $employee['lastname'];

            $datasEmployees[$employee['id_employee']]['firstname'] = $employee['firstname'];

            $datasEmployees[$employee['id_employee']]['caAvoir'] = $this->caAvoir($id_employe);

            $datasEmployees[$employee['id_employee']]['caTotal'] = $this->caTotal($id_employe);

//            $datasEmployees[$employee['id_employee']]['caRembourse'] = $this->caRembourse($id_employe);

            $datasEmployees[$employee['id_employee']]['pourCaAvoir'] = $this->pourCaAvoir($datasEmployees[$id_employe]);

//            $datasEmployees[$employee['id_employee']]['pourCaRembourse'] = $this->pourCaRembourse($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['ajustement'] = $this->ajustement($id_employe);

            $datasEmployees[$employee['id_employee']]['caImpaye'] = $this->caImpaye($id_employe);

            $datasEmployees[$employee['id_employee']]['pourCaImpaye'] = $this->pourCaImpaye($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['caAjuste'] = $this->caAjuste($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['caRembAvoir'] = $this->caRembAvoir($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['pourCaRembAvoir'] = $this->pourCaRembAvoir($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['caDeduit'] = $this->caDeduit($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['caDejaInscrit'] = $this->caDejaInscrit($id_employe);

            $datasEmployees[$employee['id_employee']]['CaProsp'] = $this->CaProsp($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['PourcCaProspect'] = $this->PourcCaProspect($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['PourcCaFID'] = $this->PourcCaFID($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['caFidTotal'] = $this->caFidTotal($id_employe);

            $datasEmployees[$employee['id_employee']]['NbreVentesTotal'] = $this->NbreVentesTotal($id_employe);

            $datasEmployees[$employee['id_employee']]['NbreDeProspects'] = $this->NbreDeProspects($id_employe);

            $datasEmployees[$employee['id_employee']]['CaContact'] = $this->CaContact($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['panierMoyen'] = $this->panierMoyen($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['nbrVenteAbo'] = $this->nbrVenteAbo($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteProsp'] = $this->nbrVenteProsp($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteFid'] = $this->nbrVenteFid($id_employe);

            $datasEmployees[$employee['id_employee']]['tauxTransfo'] = $this->tauxTransfo($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['nbrVentePar'] = $this->nbrVentePar($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteReact'] = $this->nbrVenteReact($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteCont'] = $this->nbrVenteCont($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrAbo'] = $this->nbrVenteGrAbo($id_employe);

            $datasEmployees[$employee['id_employee']]['totalVenteGrAbo'] = $this->totalVenteGrAbo($id_employe);

            $datasEmployees[$employee['id_employee']]['primeVenteGrAbo'] = $this->primeVenteGrAbo($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrDesaAbo'] = $this->nbrVenteGrDesaAbo($id_employe);

            $datasEmployees[$employee['id_employee']]['pourcenDesabo'] = $this->pourcenDesabo($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrFid'] = $this->nbrVenteGrFid($id_employe);

            $datasEmployees[$employee['id_employee']]['totalVenteGrFid'] = $this->totalVenteGrFid($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrProsp'] = $this->nbrVenteGrProsp($id_employe);

            $datasEmployees[$employee['id_employee']]['totalVenteGrProsp'] = $this->totalVenteGrProsp($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrVenteGrPar'] = $this->nbrVenteGrPar($id_employe);

            $datasEmployees[$employee['id_employee']]['primeParrainage'] = $this->primeParrainage($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['totalVenteGrPar'] = $this->totalVenteGrPar($id_employe);

            $datasEmployees[$employee['id_employee']]['pourVenteGrPar'] = $this->pourVenteGrPar($datasEmployees[$id_employe]);

            $datasEmployees[$employee['id_employee']]['primeFichierCoach'] = $this->primeFichierCoach($id_employe);

            $datasEmployees[$employee['id_employee']]['nbrJourOuvre'] = $this->nbrJourOuvre($id_employe);

        }

        $datasEmployeesTotal = array();

        if (count($datasEmployees) > 1) {
            $datasEmployeesTotal = array(
                'caAjuste' => 0,
                'caTotal' => 0,
                'caFidTotal' => 0,
                'CaProsp' => 0,
                'caDeduit' => 0,
                'primeVenteGrAbo' => 0,
                'primeFichierCoach' => 0,
                'primeParrainage' => 0,
                'ajustement' => 0,
            );

            foreach ($datasEmployees as $data) {
                $datasEmployeesTotal['caAjuste'] += $data['caAjuste'];
                $datasEmployeesTotal['caTotal'] += $data['caTotal'];
                $datasEmployeesTotal['caFidTotal'] += $data['caFidTotal'];
                $datasEmployeesTotal['CaProsp'] += $data['CaProsp'];
                $datasEmployeesTotal['caDeduit'] += $data['caDeduit'];
                $datasEmployeesTotal['primeVenteGrAbo'] += $data['primeVenteGrAbo'];
                $datasEmployeesTotal['primeFichierCoach'] += $data['primeFichierCoach'];
                $datasEmployeesTotal['primeParrainage'] += $data['primeParrainage'];
                $datasEmployeesTotal['ajustement'] += $data['ajustement'];
            }
            $datasEmployeesTotal['nbrJourOuvre'] = CaTools::getNbOpenDays($this->getDateBetween());
            $datasEmployeesTotal['id_employee'] = 0;
        }

        $ajoutSommes = CaTools::getAjoutSomme($this->idFilterCoach, $this->getDateBetween());
        $objectifCoachs = CaTools::getObjectifCoachs($this->idFilterCoach, $this->getDateBetween());
        $objectifs = CaTools::isObjectifAtteint($objectifCoachs);
        $this->smarty->assign(array(
            'datasEmployees' => ($this->histoTable) ? $this->histoTable : $datasEmployees,
            'datasEmployeesTotal' => ($this->histoMain) ? $this->histoMain : $datasEmployeesTotal,
            'ajoutSommes' => ($this->histoAjoutSomme) ? $this->histoAjoutSomme : $ajoutSommes,
            'objectifCoachs' => ($this->histoObjectif) ? $this->histoObjectif : $objectifs,
            'dateRequete' => $this->getDateBetween()
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

    private function uploadFile()
    {
        if (Tools::isSubmit('submitUpload')) {
            $error = '';
            if ($_FILES['uploadFile']['error'] == 0) {
                $helper = new HelperUploader('uploadFile');
                $files = $helper->process();
                if ($files) {
                    foreach ($files as $file) {
                        if ($file['type'] == 'text/csv') {
                            if (isset($file['save_path'])) {
                                if ($file['size'] > 3000000) {
                                    $this->errors[] = Tools::displayError('La taille du fichier est trop grande.');
                                }
                                if (!$this->errors) {
                                    if (($handle = fopen($file['save_path'], "r")) !== FALSE) {
                                        $datas = array();
                                        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                                            $datas[] = $data;
                                        }
                                    }
                                    fclose($handle);
                                    if ($this->importFile($datas)) {
                                        $this->confirmations = $this->module->l('Fichier importé');
                                    } else {
                                        $this->errors[] = Tools::displayError('Erreur lors de l\'import du fichier');
                                    }
                                }
                            }
                        } else {
                            $this->errors[] = Tools::displayError('Seul les fichiers au format csv sont autorisés.');
                        }
                        unlink($file['save_path']);
                    }
                }
            }
        }

    }

    private function importfile($datas)
    {
        $isOk = true;
        if ($datas[0][0] == 'Date valeur' &&
            $datas[0][1] == 'libellé' &&
            $datas[0][2] == 'Nom' &&
            $datas[0][4] == 'CMD' &&
            $datas[0][5] == 'Coach' &&
            $datas[0][6] == 'Reste à payer'
        ) {
            array_shift($datas);

            foreach ($datas as $data) {
                $data[0] = CaTools::convertDate($data[0]);
                if (!Validate::isDateFormat($data[0])) {
                    $isOk = false;
                }

                $reqEmploye = new DbQuery();
                $reqEmploye->select('DISTINCT id_employee')
                    ->from('employee')
                    ->where('lastname = "' . pSQL($data[5]) . '"');

                $listCoachs = Db::getInstance()->executeS($reqEmploye);
                if (count($listCoachs) != 1) {
                    $isOk = false;
                };

                if ($isOk) {
                    $aj = new AjoutSomme();
                    $aj->date_ajout_somme = $data[0];
                    $aj->commentaire = pSQL($data[1] . '-' . $data[3] . ' - ' . $data[2] . ' - ' . $data['5']);
                    $aj->id_order = (int)$data[4];
                    $aj->id_employee = (int)$listCoachs[0]['id_employee'];
                    $aj->somme = (float)$data[6];
                    $aj->impaye = 1;
                    if (!$aj->save()) {
                        $isOk = false;
                    }
                }
            }


        } else {
            $isOk = false;
        }


        return $isOk;
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
    private function ajoutSomme()
    {
        if ($this->module->viewAllCoachs[$this->context->employee->id_profile]) {
            if (Tools::isSubmit('as_submit')) {
                $data = array(
                    'id_employee' => (int)Tools::getValue('as_id_employee'),
                    'id_order' => (int)Tools::getValue('as_order'),
                    'somme' => Tools::getValue('as_somme'),
                    'commentaire' => pSQL(Tools::getValue('as_commentaire')),
                    'date_ajout_somme' => Tools::getValue('as_date')
                );
                if (!Validate::isInt($data['id_employee'])) {
                    $this->errors[] = 'L\'id de l\'employee n\'est pas valide';
                }
                if (!Validate::isInt($data['id_order'])) {
                    $this->errors[] = 'L\'id de la commande n\'est pas valide';
                }
                if (!Validate::isFloat(str_replace(',', '.', $data['somme']))) {
                    $this->errors[] = 'La somme n\'est pas valide';
                }
                if (!Validate::isString($data['commentaire'])) {
                    $this->errors[] = 'Erreur du champ commentaire';
                }
                if (!Validate::isDate($data['date_ajout_somme'])) {
                    $this->errors[] = 'Erreur du champ date';
                }

                if (!$this->errors) {
                    // Modifie
                    if (Tools::getValue('as_id')) {
                        $data['id_ajout_somme'] = (int)Tools::getValue('as_id');
                        if (!Db::getInstance()->update(
                            'ajout_somme',
                            $data,
                            'id_ajout_somme = ' . (int)Tools::getValue('as_id')
                        )
                        ) {
                            $this->errors[] = $this->l('Erreur lors de la mise à jour');
                        }
                    } else {
                        // Insert
                        if (!Db::getInstance()->insert('ajout_somme', $data)
                        ) {
                            $this->errors[] = $this->l('Erreur lors de l\'ajout.');
                        }
                    }
                    if (!$this->errors) {
                        $this->confirmation = $this->l('Enregistrement éffectué.');
                        unset($_POST['as_id_employee']);
                        unset($_POST['as_somme']);
                        unset($_POST['as_order']);
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
                $_POST['as_order'] = $as['id_order'];
                $_POST['as_commentaire'] = $as['commentaire'];
                $_POST['as_date'] = $as['date_ajout_somme'];
                $_POST['as_id'] = $as['id_ajout_somme'];
            }
        }

        $this->smarty->assign(array(
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));
    }

    /**
     * Enregistre, modifie, ou efface une ligne de la table objectif_coach
     */
    private function ajoutObjectif()
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
                        if (!Db::getInstance()->update(
                            'objectif_coach',
                            $data,
                            'id_objectif_coach = ' . (int)Tools::getValue('oc_id')
                        )
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
                $_POST['oc_jour_ouvre'] = $oc['jour_ouvre'];
                $_POST['oc_date_start'] = $oc['date_start'];
                $_POST['oc_date_end'] = $oc['date_end'];
                $_POST['oc_id'] = $oc['id_objectif_coach'];
            }
        }

        $this->smarty->assign(array(
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));
    }

    public function postProcess()
    {
        $this->processDateRange();
        $this->setIdFilterCoach();
        $this->uploadFile();
        $this->setIdFilterCodeAction();
        $this->setFilterCommandeValid();
        $this->ajoutSomme();
        $this->ajoutObjectif();
        $this->viewHistorique();


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
        $d_start = "'" . date('Y-m-d H:i:s', strtotime(Tools::substr($d, 2, 19) . ' - ' . $days . ' days')) . "'";
        $d_end = "'" . date('Y-m-d H:i:s', strtotime(Tools::substr($d, 28, 19) . ' - ' . $days . ' days')) . "'";

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
            if ((!Validate::isDate($from = Tools::getValue('datepickerFrom')) ||
                    !Validate::isDate($to = Tools::getValue('datepickerTo'))) ||
                (strtotime($from) > strtotime($to))
            ) {
                $this->errors[] = Tools::displayError('The specified date is invalid.');
            }
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
            if (!$this->isXmlHttpRequest()) {
                Tools::redirectAdmin($_SERVER['REQUEST_URI']);
            }
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
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

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
        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/views/templates/hook/pdf/content.tpl');
        $pdf->writeHTML($html_content);

        if ($this->idFilterCoach == 0 && !$this->histoMain ||
            (isset($this->histoMain['filterCoach']) && $this->histoMain['filterCoach'] === 'Tous les coachs')
        ) {
            $pdf->AddPage();
        }

        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/views/templates/hook/pdf/main_table_coachs.tpl');
        $pdf->writeHTML($html_content);

        if ($this->idFilterCoach == 0 && !$this->histoMain ||
            (isset($this->histoMain['filterCoach']) && $this->histoMain['filterCoach'] === 'Tous les coachs')
        ) {
            $pdf->AddPage();
        }

        $html_content = $this->smarty->fetch(_PS_MODULE_DIR_ . 'cdmoduleca/views/templates/hook/pdf/main_table_groupes.tpl');
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
        $name = Tools::substr($this->getDateBetween(), 2, 10) . '_' . Tools::substr($this->getDateBetween(), 28, 10) . '_';
        if ($this->idFilterCoach == 0 && !$this->histoMain) {
            $name .= 'tous_les_coachs';
        } elseif ($this->idFilterCoach != 0 && !$this->histoMain) {
            $e = new Employee($this->idFilterCoach);
            $name .= $e->lastname . '_' . $e->firstname;
        } elseif ($this->histoMain != false) {
            $name .= $this->histoMain['filterCoach'];
        }

        setlocale(LC_ALL, "en_US.utf8");
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        setlocale(LC_ALL, "fr_FR.utf8");

        return utf8_decode($name);
    }

    private function caAvoir($id_employee)
    {
        $r = CaTools::getCaCoachsAvoir($id_employee, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function pourCaAvoir($caRembourse)
    {
        $r = ($caRembourse['caTotal'] != 0) ? round((($caRembourse['caAvoir'] * 100)
                / $caRembourse['caTotal']), 2) . ' %' : '';
        return ($r != 0) ? $r : '';
    }

    private function caTotal($id_employee)
    {
        $r = CaTools::getCaCoachsTotal($id_employee, 99, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function caRembourse($id_employee)
    {
        $r = CaTools::getCaCoachsRembourse($id_employee, 0, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function pourCaRembourse($caRembourse)
    {
        $r = ($caRembourse['caTotal'] != 0) ? round((($caRembourse['caRembourse'] * 100)
                / $caRembourse['caTotal']), 2) . ' %' : '';
        return ($r != 0) ? $r : '';
    }

    private function ajustement($id_employee)
    {
        $r = CaTools::getAjustement($id_employee, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function caImpaye($id_employee)
    {
        $r = AjoutSomme::getImpaye($id_employee, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function pourCaImpaye($id_employee)
    {
        $r = ($id_employee['caTotal'] != 0) ? round((($id_employee['caImpaye'] * 100)
            / $id_employee['caTotal']), 2) : '';

        return ($r != 0) ? $r . ' %' : '';
    }

    private function caAjuste($id_employee)
    {
        $r = ($id_employee['caTotal'] > 0)
            ? ($id_employee['caTotal'])
            - $id_employee['caAvoir']
            - $id_employee['caImpaye'] : '';

        return ($r != 0) ? $r : '';
    }

    private function caRembAvoir($id_employee)
    {
        $r = ($id_employee['caTotal']) ? $id_employee['caAvoir'] : '';
        return ($r != 0) ? $r : '';
    }

    private function pourCaRembAvoir($id_employee)
    {
        $r = ($id_employee['caTotal'] != 0) ? round((($id_employee['caAvoir'] * 100)
            / $id_employee['caTotal']), 2) : '';

        return ($r != 0) ? $r . ' %' : '';
    }

    private function caDeduit($id_employee)
    {
        $r = ($id_employee['caImpaye']
            + $id_employee['caAvoir'] > 0)
            ? $id_employee['caImpaye']
            + $id_employee['caAvoir'] : '';

        return ($r != 0) ? $r : '';
    }

    private function caDejaInscrit($id_employee)
    {
        $r = CaTools::getCaDejaInscrit($id_employee, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function CaProsp($id_employee)
    {
        $r = CaTools::caProsp($id_employee);
        return ($r != 0) ? $r : '';
    }

    private function PourcCaProspect($id_employee)
    {
        $r = CaTools::pourcCaProspect($id_employee);
        return ($r != 0) ? $r : '';
    }

    private function PourcCaFID($id_employee)
    {
        $r = CaTools::pourcCaFID($id_employee);
        return ($r != 0) ? $r : '';
    }

    private function caFidTotal($id_employe)
    {
        $r = CaTools::getCaDejaInscrit($id_employe, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function NbreVentesTotal($id_employe)
    {
        $r = CaTools::getNumberCommande($id_employe, null, array(460, 443), $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function NbreDeProspects($id_employe)
    {
        $r = ProspectAttribueClass::getNbrProspectsAttriByCoach($id_employe, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function CaContact($id_employe)
    {
        $r = ($id_employe['NbreDeProspects'])
            ? round((($id_employe['caAjuste'] - $id_employe['caFidTotal'])
                / $id_employe['NbreDeProspects']), 2)
            : '';

        return ($r != 0) ? $r : '';
    }

    private function panierMoyen($id_employe)
    {
        $r = CaTools::getPanierMoyen($id_employe);
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteAbo($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'ABO', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteProsp($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'Prosp', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteFid($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'FID', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function tauxTransfo($id_employe)
    {
        $r = ($id_employe['NbreDeProspects'] != 0)
            ? (round(((($id_employe['NbreVentesTotal'] - $id_employe['nbrVenteFid']) * 100)
                / $id_employe['NbreDeProspects']), 2))
            : '';

        return ($r != 0) ? $r . ' %' : '';
    }

    private function nbrVentePar($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'PAR', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteReact($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'REACT+4M', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteCont($id_employe)
    {
        $r = CaTools::getNbrVentes($id_employe, 'CONT ENTR', $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrVenteGrAbo($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'ABO',
            array(444, 462),
            false,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function totalVenteGrAbo($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'ABO',
            array(444, 462),
            true,
            true,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function primeVenteGrAbo($id_employe)
    {
        $n = $id_employe['totalVenteGrAbo'];
        $prime = ($n) ? ($n / 100) * 10 : ''; // Calcul de la prime 10 % sur la vente des abos

        return ($prime != 0) ? $prime : '';
    }

    private function nbrVenteGrDesaAbo($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'ABO',
            array(440, 453),
            false,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function pourcenDesabo($id_employe)
    {
        $r = ($id_employe['nbrVenteGrAbo'])
            ? round((($id_employe['nbrVenteGrDesaAbo'] * 100) / $id_employe['nbrVenteGrAbo']), 2)
            : '';

        return ($r != 0) ? $r . ' %' : '';
    }

    private function nbrVenteGrFid($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'FID',
            null,
            false,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function totalVenteGrFid($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'FID',
            null,
            true,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function nbrVenteGrProsp($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'PROSP',
            null,
            false,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function totalVenteGrProsp($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'PROSP',
            null,
            true,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function nbrVenteGrPar($id_employe)
    {
        $r = CaTools::getNbrGrVentes(
            $id_employe,
            'PAR',
            null,
            false,
            false,
            $this->getDateBetween(),
            $this->module->lang
        );

        return ($r != 0) ? $r : '';
    }

    private function primeParrainage($id_employe)
    {
        if (!empty($id_employe['nbrVenteGrPar'])) {
            $prime = Configuration::get('CDMODULECA_PRIME_PARRAINAGE');
            return $prime * $id_employe['nbrVenteGrPar'];
        }
        return '';
    }

    private function totalVenteGrPar($id_employe)
    {
        $r = CaTools::getParrainage(
            $id_employe,
            'PAR',
            $this->getDateBetween()
        );

        return ($r != 0) ? $r : '';
    }

    private function pourVenteGrPar($id_employe)
    {
        $r = ($id_employe['totalVenteGrPar'])
            ? round(($id_employe['totalVenteGrPar'] * 100) / $id_employe['caAjuste'], 2)
            : '';

        return ($r != 0) ? $r . ' %' : '';
    }

    private function primeFichierCoach($id_employe)
    {
        $r = CaTools::primeFichier($id_employe, $this->getDateBetween());
        return ($r != 0) ? $r : '';
    }

    private function nbrJourOuvre($id_employe)
    {
        $nbrjourOuvre = CaTools::getJourOuvreEmploye($id_employe, $this->getDateBetween());
        $r = (empty($nbrjourOuvre))
            ? CaTools::getNbOpenDays($this->getDateBetween())
            : $nbrjourOuvre;

        return ($r != 0) ? $r : '';
    }

    private function historique()
    {
        $histoMain = $this->insertHistoMain();
        $this->insertHistoAjoutSomme($histoMain->id);
        $this->insertHistoObjectifCoach($histoMain->id);
        $this->insertHistoStatsTable($histoMain->id);

    }

    private function convertFloat($number)
    {
        return str_replace(',', '.', $number);
    }

    private function insertHistoMain()
    {
        $histoMain = new HistoStatsMainClass();
        if (isset($this->smarty->tpl_vars['datasEmployeesTotal']->value['caAjuste'])) {
            $data = $this->smarty->tpl_vars['datasEmployeesTotal'];
            $histoMain->filterCoach = 'Tous les coachs';
            $histoMain->datepickerFrom = substr($this->getDateBetween(), 2, 10);
            $histoMain->datepickerTo = substr($this->getDateBetween(), 28, 10);
            $histoMain->id_employee = $data->value['id_employee'];
            $histoMain->caAjuste = $this->convertFloat($data->value['caAjuste']);
            $histoMain->caTotal = $this->convertFloat($data->value['caTotal']);
            $histoMain->caFidTotal = $this->convertFloat($data->value['caFidTotal']);
            $histoMain->CaProsp = $this->convertFloat($data->value['CaProsp']);
            $histoMain->caDeduit = $this->convertFloat($data->value['caDeduit']);
            $histoMain->primeVenteGrAbo = $this->convertFloat($data->value['primeVenteGrAbo']);
            $histoMain->primeFichierCoach = $this->convertFloat($data->value['primeFichierCoach']);
            $histoMain->primeParrainage = $this->convertFloat($data->value['primeParrainage']);
            $histoMain->ajustement = $this->convertFloat($data->value['ajustement']);
            $histoMain->nbrJourOuvre = (int)$data->value['nbrJourOuvre'];
        } else {
            foreach ($this->smarty->tpl_vars['datasEmployees']->value as $data) {
                $histoMain->filterCoach = $data['lastname'];
                $histoMain->id_employee = $data['id_employee'];
                $histoMain->datepickerFrom = substr($this->getDateBetween(), 2, 10);
                $histoMain->datepickerTo = substr($this->getDateBetween(), 28, 10);
                $histoMain->caAjuste = $this->convertFloat($data['caAjuste']);
                $histoMain->caTotal = $data['caTotal'];
                $histoMain->caFidTotal = $this->convertFloat($data['caFidTotal']);
                $histoMain->CaProsp = $this->convertFloat($data['CaProsp']);
                $histoMain->caDeduit = $this->convertFloat($data['caDeduit']);
                $histoMain->primeVenteGrAbo = $this->convertFloat($data['primeVenteGrAbo']);
                $histoMain->primeFichierCoach = $this->convertFloat($data['primeFichierCoach']);
                $histoMain->primeParrainage = $this->convertFloat($data['primeParrainage']);
                $histoMain->ajustement = $this->convertFloat($data['ajustement']);
                $histoMain->nbrJourOuvre = (int)$data['nbrJourOuvre'];
            }
        }
        $histoMain->save();

        return $histoMain;
    }

    private function insertHistoAjoutSomme($id)
    {
        $datas = $this->smarty->tpl_vars['ajoutSommes']->value;
        foreach ($datas as $data) {
            $ajout = new HistoAjoutSommeClass();
            $ajout->id_histostatsmain = $id;
            $ajout->lastname = $data['lastname'];
            $ajout->date_ajout_somme = substr($data['date_ajout_somme'], 0, 10);
            $ajout->somme = $this->convertFloat($data['somme']);
            $ajout->commentaire = $data['commentaire'];
            $ajout->id_order = $data['id_order'];
            $ajout->save();
        }
    }

    private function insertHistoObjectifCoach($id)
    {
        $datas = $this->smarty->tpl_vars['objectifCoachs']->value;
        foreach ($datas as $data) {
            $objectif = new HistoObjectifCoachClass();
            $objectif->id_histostatsmain = $id;
            $objectif->lastname = $data['lastname'];
            $objectif->class = $data['class'];
            $objectif->date_start = $data['date_start'];
            $objectif->date_end = $data['date_end'];
            $objectif->somme = $this->convertFloat($data['somme']);
            $objectif->caCoach = $this->convertFloat($data['caCoach']);
            $objectif->pourcentDeObjectif = $this->convertFloat($data['pourcentDeObjectif']);
            $objectif->heure_absence = (int)$data['heure_absence'];
            $objectif->jour_absence = (int)$data['jour_absence'];
            $objectif->jour_ouvre = (int)$data['jour_ouvre'];
            $objectif->commentaire = $data['commentaire'];
            $objectif->save();
        }

    }

    private function insertHistoStatsTable($id)
    {
        $datas = $this->smarty->tpl_vars['datasEmployees']->value;
        foreach ($datas as $data) {
            $table = new HistoStatsTableClass();
            $table->id_histostatsmain = $id;
            $table->lastname = $data['lastname'];
            $table->firstname = $data['firstname'];
            $table->caAjuste = $this->convertFloat($data['caAjuste']);
            $table->CaContact = $this->convertFloat($data['CaContact']);
            $table->tauxTransfo = $this->convertFloat($data['tauxTransfo']);
            $table->NbreDeProspects = $data['NbreDeProspects'];
            $table->NbreVentesTotal = $data['NbreVentesTotal'];
            $table->CaProsp = $this->convertFloat($data['CaProsp']);
            $table->PourcCaProspect = $this->convertFloat($data['PourcCaProspect']);
            $table->caDejaInscrit = $this->convertFloat($data['caDejaInscrit']);
            $table->PourcCaFID = $this->convertFloat($data['PourcCaFID']);
            $table->panierMoyen = $this->convertFloat($data['panierMoyen']);
            $table->caAvoir = $this->convertFloat($data['caAvoir']);
            $table->pourCaAvoir = $this->convertFloat($data['pourCaAvoir']);
            $table->caImpaye = $this->convertFloat($data['caImpaye']);
            $table->pourCaImpaye = $this->convertFloat($data['pourCaImpaye']);
            $table->totalVenteGrAbo = $this->convertFloat($data['totalVenteGrAbo']);
            $table->nbrVenteGrAbo = $data['nbrVenteGrAbo'];
            $table->nbrVenteGrDesaAbo = $data['nbrVenteGrDesaAbo'];
            $table->pourcenDesabo = $this->convertFloat($data['pourcenDesabo']);
            $table->totalVenteGrPar = $this->convertFloat($data['totalVenteGrPar']);
            $table->nbrVenteGrPar = $data['nbrVenteGrPar'];
            $table->pourVenteGrPar = $this->convertFloat($data['pourVenteGrPar']);
            $table->save();
        }
    }

    private function viewHistorique()
    {
        if (Tools::isSubmit('id_histo')) {
            $id = (int)Tools::getValue('id_histo');
            $histoMain = get_object_vars(new HistoStatsMainClass($id));
            // Est-ce que le coach peut voir l'historiqe ?
            if ($this->module->viewAllCoachs[$this->context->employee->id_profile] ||
                $this->context->employee->id == $histoMain['id_employee']
            ) {
                $this->histoMain = $histoMain;
                $histoAjoutSomme = HistoAjoutSommeClass::getAjoutSomme($id);
                $histoObjectif = HistoObjectifCoachClass::getObjectif($id);
                $histoTable = HistoStatsTableClass::getTable($id);

                $this->histoAjoutSomme = ($histoAjoutSomme) ? $histoAjoutSomme : true;
                $this->histoObjectif = ($histoObjectif) ? $histoObjectif : true;
                $this->histoTable = ($histoTable) ? $histoTable : true;


                $this->smarty->assign(array(
                    'histo' => 'true',
                ));
            }
        }
    }
}


class MYPDF extends TCPDF
{

    // Page footer
    public function footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
