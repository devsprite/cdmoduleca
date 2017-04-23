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
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2016 Chez-Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit();
}

require_once(dirname(__FILE__) . '/controllers/admin/AdminCaLetSens.php');

class CdModuleCA extends ModuleGrid
{
    public $errors = array();
    public $confirmation = '';
    public $html = '';
    public $query;
    public $columns;
    public $default_sort_column;
    public $default_sort_direction;
    public $empty_message;
    public $paging_message;
    public $viewAllCoachs; // Filtre l'affichage entre employé et manager
    public $idFilterCoach; // Id du coach sélectionné dans Stats -> L&Sens
    public $idFilterCodeAction; // Id du code action sélectionné dans Stats -> L&Sens
    public $commandeValid; // Selecteur commande valide ou pas dans Stats -> L&Sens
    public $employees_actif;
    public $lang;
    public $config = array(
        'CDMODULECA' => '1',
        'CDMODULECA_ORDERS_STATE' => '7', // Statut pris en compte pour la déduction du CA
        'CDMODULECA_ORDERS_STATE_JOURS' => '60', // Nombre de jours avant la prise en compte de la commande
        'CDMODULECA_PRIME_FICHIER' => '0.5', // Montant de la prime fichier
        'CDMODULECA_PRIME_PARRAINAGE' => '5', // Montant de la prime fichier
        'CDMODULECA_PROSPECTS_JOUR' => '26', // Nombre de prospects par jour et par coach
        'CDMODULECA_PROSPECTS_HEURE' => '4.33', // Nombre de prospects par heure et par coach
        'CDMODULECA_NBR_JOUR_MAX_PROSPECTS' => '10', // Nombre de jour avant la date index (ancien prospects)
        'CDMODULECA_PROSPECTS_INDEX_DATE' => '2016-11-08 00:00:00', // Date pour la prise en compte des nouveaux prospects
        'CDMODULECA_SEUIL_PRIME_FICHIER' => '18'
    );

    public function __construct()
    {
        $this->name = 'cdmoduleca';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.22';
        $this->author = 'Dominique';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->lang = Configuration::get('PS_LANG_DEFAULT');
        parent::__construct();

        $this->displayName = $this->l('Module CA');
        $this->description = $this->l('Synthèse CA pour L et Sens');
        $this->confirmUninstall = $this->l('Etes-vous sur ? Prestashop risque de ne plus fonctionner tant que les overrides ne seront pas traités.');
        // Ajuste les permissions pour accéder au contenu de la page stat
        $this->viewAllCoachs = array(
            '1' => true,    // SuperAdmin
            '2' => false,   // Logisticien
            '3' => false,   // Traducteur
            '4' => false,   // Commercial
            '5' => false,   // Diet
            '6' => false,   // Stagiaire
            '7' => true,    // Manager
            '8' => false    // WebMarketing
        );
        $this->empty_message = $this->l('Pas d\'enregistrement disponible');
        $this->paging_message = sprintf($this->l('Affichage %1$s de %2$s'), '{0} - {1}', '{2}');
        $this->limit = 800;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->table_charset = 'utf8';
        $this->default_sort_column = 'id';
        $this->default_sort_direction = 'DESC';
        $this->employees_actif = 1;
        $this->commandeValid = 1;
        $this->columns = array(
            array(
                'id' => 'id',
                'header' => $this->l('Commande'),
                'dataIndex' => 'id',
                'align' => 'center',
                'data-sort' => 'int'
            ),
            array(
                'id' => 'id_customer',
                'header' => $this->l('Client'),
                'dataIndex' => 'id_customer',
                'align' => 'left',
                'data-sort' => 'string-ins'
            ),
            array(
                'id' => 'impaye',
                'header' => $this->l('Impayé'),
                'dataIndex' => 'impaye',
                'align' => 'right',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'avoir',
                'header' => $this->l('Avoir'),
                'dataIndex' => 'avoir',
                'align' => 'right',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'ajustement',
                'header' => $this->l('Ajustement'),
                'dataIndex' => 'ajustement',
                'align' => 'right',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'hthp',
                'header' => $this->l('ht-hp'),
                'dataIndex' => 'hthp',
                'align' => 'right',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'id_employee',
                'header' => $this->l('Coach'),
                'dataIndex' => 'id_employee',
                'align' => 'center',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'commentaire',
                'header' => $this->l('Commentaire'),
                'dataIndex' => 'commentaire',
                'align' => 'left',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'current_state',
                'header' => $this->l('Etat Commande'),
                'dataIndex' => 'current_state',
                'align' => 'left',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'CodeAction',
                'header' => $this->l('Code action'),
                'dataIndex' => 'CodeAction',
                'align' => 'left',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'new',
                'header' => $this->l('Nouveau Client'),
                'dataIndex' => 'new',
                'align' => 'center',
                'data-sort' => 'float'
            ),
            array(
                'id' => 'date_add',
                'header' => $this->l('Date'),
                'dataIndex' => 'date_add',
                'align' => 'center',
                'data-sort' => 'float'
            ),
        );
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->createTabsStatsCA() ||
            !$this->createTabsProspects() ||
            !$this->createTabsAppel() ||
            !$this->createTableProspectAttribue() ||
            !$this->createTableProspect() ||
            !$this->createTableAppel() ||
            !$this->createTableObjectifCoach() ||
            !$this->createTableAjoutSomme() ||
            !$this->alterGroupLangTable() ||
            !$this->installConfigGroupes() ||
            !$this->alterOrderTable() ||
            !$this->createCodeActionTable() ||
            !$this->updateOrdersTable() ||
            !$this->installConfig() ||
            !$this->registerHook('displayBackOfficeTop') ||
            !$this->registerHook('ActionValidateOrder')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (
            !parent::uninstall() ||
            !$this->eraseTabsStatsCA() ||
            !$this->eraseTabsProspects() ||
            !$this->eraseTabsAppel() ||
            !$this->eraseTabsHistorique() ||
            !$this->eraseTableProspectAttribue() ||
            !$this->eraseTableProspect() ||
            !$this->eraseTableAppel() ||
            !$this->eraseTableObjectifCoach() ||
            !$this->eraseTableAjoutSomme() ||
            !$this->removeCodeActionTable() ||
            !$this->alterGroupLangTable('remove') ||
            !$this->alterOrderTable('remove') ||
            !$this->eraseConfig()
        ) {
            return false;
        }

        return true;
    }

    /**
     * historique des appels passés via les liens Keyyo
     * @return bool
     */
    private function createTabsAppel()
    {
        $tab = new Tab();
        $tab->active = 1;
        $names = array(1 => 'Appels', 'Appels');
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = isset($names[$language['id_lang']])
                ? $names[$language['id_lang']] : $names[1];
        }
        $tab->class_name = 'AdminAppel';
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminParentStats');

        return (bool)$tab->add();
    }

    private function eraseTabsAppel()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAppel');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    /**
     * historique du module CA
     * @return bool
     */
    public function createTabsHistorique()
    {
        $tab = new Tab();
        $tab->active = 1;
        $names = array(1 => 'Historique CA', 'Historique CA');
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = isset($names[$language['id_lang']])
                ? $names[$language['id_lang']] : $names[1];
        }
        $tab->class_name = 'AdminHistorique';
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminParentStats');

        return (bool)$tab->add();
    }

    private function eraseTabsHistorique()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAppel');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    /**
     * Lien prospects dans le menu Clients
     * @return bool
     */
    private function createTabsProspects()
    {
        $tab = new Tab();
        $tab->active = 1;
        $names = array(1 => 'Prospects', 'Prospects');
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = isset($names[$language['id_lang']])
                ? $names[$language['id_lang']] : $names[1];
        }
        $tab->class_name = 'AdminProspects';
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminCustomers');

        return (bool)$tab->add();
    }

    private function eraseTabsProspects()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminProspects');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    /**
     * Compteur d'appels dans la barre du haut
     * @return string
     */
    public function hookDisplayBackOfficeTop()
    {
        $objectifCoach = array();
        $this->context->controller->addCSS($this->_path . 'views/css/compteur.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/compteur.js');
        $objectifCoach[] = CaTools::getObjectifCoach($this->context->employee->id);
        $objectif = CaTools::isProjectifAtteint($objectifCoach);
        $objectif[0]['appels'] = (isset($_COOKIE['appelKeyyo'])) ? (int)$_COOKIE['appelKeyyo'] : '0';

        $this->smarty->assign(array(
            'objectif' => $objectif[0]
        ));
        return $this->display(__FILE__, 'compteur.tpl');
    }

    /**
     * Lien CA L&Sens dans le menu Stats
     * @return bool
     */
    private function createTabsStatsCA()
    {
        $tab = new Tab();
        $tab->active = 1;
        $names = array(1 => 'CA L&Sens', 'CA L&Sens');
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = isset($names[$language['id_lang']])
                ? $names[$language['id_lang']] : $names[1];
        }
        $tab->class_name = 'AdminCaLetSens';
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminParentStats');

        return (bool)$tab->add();
    }

    private function eraseTabsStatsCA()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminCaLetSens');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        return true;
    }

    /**
     * Table pour ajouter une ligne d'attribution de prospects à un employee
     * @return bool
     */
    private function createTableProspectAttribue()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'prospect_attribue` (
        `id_prospect_attribue` INT(12) NOT NULL AUTO_INCREMENT,
        `id_employee` INT(12) NOT NULL,
        `nbr_prospect_attribue` INT(12) NULL,
        `date_debut` DATETIME NOT NULL,
        `date_fin` DATETIME NOT NULL,
        PRIMARY KEY (`id_prospect_attribue`))
        ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $this->table_charset . ';';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function eraseTableProspectAttribue()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'prospect_attribue`');
    }

    /**
     * Table Historique des appels passé via les liens Keyyo
     * @return bool
     */
    private function createTableAppel()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'appel` (
        `id_appel` INT(12) NOT NULL AUTO_INCREMENT,
        `id_employee` INT(12) NOT NULL,
        `compteur` INT(12) NULL,
        `date_upd` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_appel`))
        ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $this->table_charset . ';';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function eraseTableAppel()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'appel`');
    }

    /**
     * Table pour le suivi des prospect attribué au employes
     * @return bool
     */
    private function createTableProspect()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'prospect` (
        `id_prospect` INT(12) NOT NULL AUTO_INCREMENT,
        `id_customer` INT(12) NOT NULL UNIQUE,
        `id_prospect_attribue` INT(12) NOT NULL,
        `traite` TEXT NULL,
        `injoignable` TEXT NULL,
        `contacte` TEXT NULL,
        `date_debut` DATETIME NOT NULL,
        `date_add` DATETIME NOT NULL,
        PRIMARY KEY (`id_prospect`))
        ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $this->table_charset . ';';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function eraseTableProspect()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'prospect`');
    }

    /**
     * Table permettant l'ajout d'un objectif, des absences, jour ouvré pour un employee
     * @return bool
     */
    private function createTableObjectifCoach()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "objectif_coach` (
        `id_objectif_coach` INT (12) NOT NULL AUTO_INCREMENT,
        `id_employee` INT(12),
        `somme` DECIMAL (8,2) NULL,
        `commentaire` TEXT NULL,
        `heure_absence` DECIMAL(6,2) NULL,
        `jour_absence` INT(12) NULL,
        `jour_ouvre` INT(12) NULL,
        `date_start` DATETIME NOT NULL,
        `date_end` DATETIME NOT NULL,
        PRIMARY KEY (`id_objectif_coach`))
        ENGINE =" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=" . $this->table_charset . ";";

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    private function eraseTableObjectifCoach()
    {
        if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'objectif_coach`')) {
            return false;
        }
        return true;
    }

    /**
     * Table permettant l'ajout ou la déduction d'une prime pour un employee, ou des impayés par import csv
     * @return bool
     */
    private function createTableAjoutSomme()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "ajout_somme` (
        `id_ajout_somme` INT (12) NOT NULL AUTO_INCREMENT,
        `id_employee` INT (12),
        `id_order` INT (12),
        `impaye` INT (12),
        `somme` DECIMAL (8,2) NULL,
        `commentaire` TEXT NULL,
        `date_ajout_somme` DATETIME NOT NULL,
        `date_add` DATETIME NOT NULL,
        `date_upd` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_ajout_somme`))
        ENGINE =" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=" . $this->table_charset . ";";

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    private function eraseTableAjoutSomme()
    {
        if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ajout_somme`')) {
            return false;
        }
        return true;
    }

    /**
     * Ajout d'une colonne id_employee pour faire la liaison entre les employee et les groupes client
     * @return bool
     */
    private function alterGroupLangTable($method = 'add')
    {
        if ($method == 'add') {
            $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'group_lang ADD `id_employee` VARCHAR (255) NULL';
        } else {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'group_lang` DROP COLUMN `id_employee`';
        }
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Table permettant de regrouper les codes actions
     * @return bool
     */
    private function createCodeActionTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'code_action` (
        `id_code_action` INT(12) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(64) NOT NULL,
        `description` VARCHAR(255) NULL,
        `groupe` VARCHAR(64) NULL, 
        PRIMARY KEY (`id_code_action`))
          ENGINE =' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=' . $this->table_charset . ';';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        $data = $this->dataCodeAction();
        if (!Db::getInstance()->insert('code_action', $data)) {
            return false;
        }

        return true;
    }

    private function removeCodeActionTable()
    {
        if (!Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'code_action`')) {
            return false;
        }

        return true;
    }

    /**
     * Configuration du regroupement des codes actions
     * @return array
     */
    private function dataCodeAction()
    {
        $code_action = array(
            'ABO' => array('Abonnement', '1'),
            'PROSP' => array('Prospection', '2'),
            'PROSP1' => array('Prospection tracer 1', '2'),
            'PROSP11' => array('Prospection tracer 11', '2'),
            'PROSP12' => array('Prospection tracer 12', '2'),
            'PROSP13' => array('Prospection tracer 13', '2'),
            'PROSP2' => array('Prospection tracer 2', '2'),
            'PROSP21' => array('Prospection tracer 21', '2'),
            'PROSP22' => array('Prospection tracer 22', '2'),
            'PROSP23' => array('Prospection tracer 23', '2'),
            'PROSP24' => array('Prospection tracer 24', '2'),
            'PROSP3' => array('Prospection tracer 3', '2'),
            'PROSP5' => array('Prospection tracer 5', '2'),
            'PROSP51' => array('Prospection tracer 51', '2'),
            'PROSP52' => array('Prospection tracer 52', '2'),
            'PROSP53' => array('Prospection tracer 53', '2'),
            'PROSP ENTR' => array('Contact entrant sans fiche client', '2'),
            'PROSP REL' => array('Prospection REL', '2'),
            'PROSPWEB' => array('Prospection Web', '2'),
            'FID' => array('FID', '20'),
            'FID PROMO' => array('FID suite promo', '20'),
            'FID PROG F' => array('FID programme fidélité', '20'),
            'FID WEB PRGF' => array('FID web PRGF', '20'),
            'FID WEB PROMO' => array('FID web PROMO', '20'),
            'FID WEB PROM' => array('FID web PROMO', '20'),
            'FID WEB' => array('FID web', '20'),
            'PAR' => array('Parrainage', '27'),
            'REACT+4M' => array('Reactivation fichier clients +4mois', '28'),
            'REACT+4MPROMO' => array('Reactivation fichier clients +4mois suite à promo', '28'),
            'REACT SPONT' => array('Reactivation client spontanée', '28'),
            'REACT SPONT PROMO' => array('Reactivation client spontanée suite à promo', '28'),
            'REACTSPONT' => array('Reactivation client AC formulaire', '28'),
            'REACT AC FORM' => array('Reactivation client AC formulaire', '28'),
            'REACTIV' => array('Reactivation REACTIV', '28'),
            'CONT ENTR' => array('CONT ENTR', '35')
        );
        $data = array();
        $c = 1;
        foreach ($code_action as $key => $value) {
            $data[] = array(
                'id_code_action' => (int)$c,
                'name' => pSQL($key),
                'description' => pSQL($value[0]),
                'groupe' => (int)$value[1]
            );
            $c++;
        }

        return $data;
    }

    /**
     * Installation d'une configuration de base des groupes client lié au employés
     * @return bool
     */
    private function installConfigGroupes()
    {
        // Configuration des groupes et employées id_groupe => id_employee
        $confGroupes = array(
            '19' => '38', // Elise
            '20' => '59', // Olivier
            '21' => '58', // Chloé
            '24' => '10', // Bénédicte
            '25' => '12', // Sophie
            '26' => '33', // Lina
            '27' => '60', // Amandine
            '29' => '15', // Mélissa
            '30' => '9',  // Eva
            '31' => '47', // Jean-Batiste
            '33' => '57', // Ludovic
            '34' => '30', // Gaëlle
            '35' => '37', // Emilie
            '36' => '23', // Julia
            '37' => '56', // Martine
            '38' => '55', // Sandra
        );

        foreach ($confGroupes as $groupe => $value) {
            Db::getInstance()->update(
                'group_lang',
                array('id_employee' => $value),
                'id_group = ' . (int)$groupe . ' AND id_lang = "' . (int)$this->lang . '"'
            );
        }

        return true;
    }

    /**
     * Permet de lié une commande avec un code action et un employee avec les id correspondants
     * @param string $method
     * @return bool
     */
    private function alterOrderTable($method = 'add')
    {
        if ($method == 'add') {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` 
            ADD `id_code_action` INT (12) NULL,
            ADD `id_employee` INT (12) NULL';
        } else {
            $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` DROP COLUMN `id_code_action`, 
             DROP COLUMN `id_employee`';
        }
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function installConfig()
    {
        foreach ($this->config as $keyname => $value) {
            Configuration::updateValue($keyname, $value);
        }

        return true;
    }

    private function eraseConfig()
    {
        foreach ($this->config as $keyname => $value) {
            Configuration::deleteByName($keyname);
        }

        return true;
    }

    /**
     * Configuration du module
     * @return string
     */
    public function getContent()
    {
        $this->updateOrdersTableIdCodeAction();
        $this->updateOrdersTableIdEmployee();
        $this->context->controller->addCSS(_PS_MODULE_DIR_ . 'cdmoduleca/views/css/bootstrap.min.css');
        $this->postProcess();
        $this->displayForm();

        return $this->html;
    }

    private function postProcess()
    {
        $error = '';
        // Mise à jour de la l'attribution des groupes lié aux employés
        if (Tools::isSubmit('submitUpdateGroups')) {
            $groups = $this->getGroupsParrain();
            foreach ($groups as $group) {
                if (!Db::getInstance()->update(
                    'group_lang',
                    array('id_employee' => (int)(str_replace('gp_', '', Tools::getValue('gp_' . $group['id_group'])))),
                    'id_lang = "' . (int)$this->lang . '" AND id_group = ' . (int)str_replace('gp_', '', $group['id_group'])
                )
                ) {
                    $error .= $this->l('Erreur lors de la mise à jour des groupes');
                }
            }
            // Mise à jour de la configuration des code action, permet de regrouper les codes actions entre eux
        } elseif (Tools::isSubmit('submitUpdateCodeAction')) {
            $codes_action = $this->getAllCodesAction();
            foreach ($codes_action as $code) {
                if (Db::getInstance()->update(
                    'code_action',
                    array('groupe' => (int)str_replace('ca_', '', Tools::getValue('ca_' . $code['id_code_action']))),
                    'id_code_action = ' . (int)str_replace('ca_', '', $code['id_code_action'])
                )
                ) {
                    $this->updateOrdersTableIdCodeAction();
                } else {
                    $error .= $this->l('Erreur lors de la mise à jour des codes action.');
                }
            }
            // Détermine quel sont les status de commande à deduire d'un employee si la commande est valide,
            // avec le nombre de jour de retard à prendre en compte
        } elseif (Tools::isSubmit('submitUpdateStatuts')) {
            $listStatuts = OrderState::getOrderStates($this->lang);
            $statuts = array();
            foreach ($listStatuts as $statut) {
                if (Tools::getValue('os_' . $statut['id_order_state'])) {
                    $statuts[] = (int)$statut['id_order_state'];
                }
            }
            $confStatus = implode(',', $statuts);
            Configuration::updateValue('CDMODULECA_ORDERS_STATE', $confStatus);
            Configuration::updateValue('CDMODULECA_ORDERS_STATE_JOURS', (int)Tools::getValue('os_nbr_jours'));
        } elseif (Tools::isSubmit('submitConfiguration')) {
            Configuration::updateValue('CDMODULECA_PRIME_FICHIER', (float)(Tools::getValue('co_prime_fichier')));
            Configuration::updateValue('CDMODULECA_SEUIL_PRIME_FICHIER', (float)(Tools::getValue('co_seuil_prime_fichier')));
            Configuration::updateValue('CDMODULECA_PROSPECTS_JOUR', (float)(Tools::getValue('co_prospects_jour')));
            Configuration::updateValue('CDMODULECA_PROSPECTS_HEURE', (float)(Tools::getValue('co_prospects_heure')));
            Configuration::updateValue('CDMODULECA_NBR_JOUR_MAX_PROSPECTS', (int)(Tools::getValue('co_prospects_jour_max')));
            Configuration::updateValue('CDMODULECA_PRIME_PARRAINAGE', (float)(Tools::getValue('co_prime_parrainage')));
        }

        if ($error) {
            $this->html .= $this->displayError($error);
        }
    }

    private function displayForm()
    {
        $this->html .= $this->generateFormConstantes();
        $this->html .= $this->generateFormGroupeParrain();
        $this->html .= $this->generateFormCodeAction();
//        $this->html .= $this->generateFormStatutsCommande(); // Désactivé car on importe un csv pour gérer les impayés
    }

    /**
     * Formulaire de configuration des valeurs fixe, prime fichier etc...
     * @return string
     */
    private function generateFormConstantes()
    {
        $inputs = array();
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Seuil de Calcul Prime fichier',
            'name' => 'co_seuil_prime_fichier',
            'desc' => 'Montant du seuil de calcul de la prime',
            'class' => 'input fixed-width-md',
            'suffix' => '€'
        );
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Prime fichier',
            'name' => 'co_prime_fichier',
            'desc' => 'Montant de la prime fichier',
            'class' => 'input fixed-width-md',
            'suffix' => '€'
        );
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Prime parrainage',
            'name' => 'co_prime_parrainage',
            'desc' => 'Montant de la prime parrainage',
            'class' => 'input fixed-width-md',
            'suffix' => '€'
        );
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Prospects par jour',
            'name' => 'co_prospects_jour',
            'desc' => 'Nombre de prospects affectés par jour travaillé',
            'class' => 'input fixed-width-md',
        );
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Prospects par heure',
            'name' => 'co_prospects_heure',
            'desc' => 'Nombre de prospects par heure travaillé',
            'class' => 'input fixed-width-md',
        );
        $inputs[] = array(
            'type' => 'text',
            'label' => 'Ancienneté des prospects',
            'name' => 'co_prospects_jour_max',
            'desc' => 'Nombre de jour maximum d\'ancienneté des prospects pour l\'atribution',
            'class' => 'input fixed-width-md',
            'suffix' => 'jour'
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitConfiguration'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigConfiguration(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));

    }

    /**
     * Formulaire de groupement des codes action
     * @return string
     */
    private function generateFormCodeAction()
    {
        $codesAction = $this->getAllCodesAction();
        $inputs = array();
        foreach ($codesAction as $code => $value) {
            $inputs[] = array(
                'type' => 'select',
                'label' => $value['description'] . ' (' . $value['name'] . ')',
                'name' => 'ca_' . $value['id_code_action'],
                'options' => array(
                    'query' => $codesAction,
                    'id' => 'id_code_action',
                    'name' => 'name'
                ),
            );
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Groupement des codes action.'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitUpdateCodeAction'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigCodeAction(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));

    }

    /**
     * Formulaire de configuration des commande considéré comme impayées, à déduire du coach
     * @return string
     */
    private function generateFormStatutsCommande()
    {
        $listStatuts = OrderState::getOrderStates($this->lang);

        $inputs = array();
        $inputs[] =
            array(
                'type' => 'text',
                'label' => 'Nombre de jours',
                'name' => 'os_nbr_jours',
                'desc' => 'Nombre de jours avant prise en compte du statut',
                'class' => 'input fixed-width-md',
            );
        foreach ($listStatuts as $statut => $value) {
            $inputs[] = array(
                'type' => 'switch',
                'label' => $value['name'] . '  ( ' . $value['id_order_state'] . ' )',
                'name' => 'os_' . $value['id_order_state'],
                'desc' => $this->l('Commandes déduite du CA du coach ?'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                )
            );
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Les commandes avec le statuts à Oui seront déduite du CA du coach.'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitUpdateStatuts'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigStatusCommandes(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    /**
     * Formulaire de liaison groupes employés
     * @return string
     */
    private function generateFormGroupeParrain()
    {
        $groupsParrain = $this->getGroupsParrain();
        $inputs = array();
        foreach ($groupsParrain as $group => $value) {
            $inputs[] = array(
                'type' => 'text',
                'label' => $value['name'] . ' ( Groupe ' . $value['id_group'] . ' )',
                'name' => 'gp_' . $value['id_group'],
                'desc' => $this->l('id employé'),
                'class' => 'input fixed-width-md',
            );
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Affecter un employé à un groupe.'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitUpdateGroups'
                )
            )
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->default_form_language = $lang->id;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
            . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigGroupsParrain(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    /**
     * retourne la configuration des statuts lié au commandes
     * @return array
     */
    private function getConfigStatusCommandes()
    {
        $statuts = array();
        $list_statuts = OrderState::getOrderStates($this->lang);
        $conf_statuts = explode(',', Configuration::get('CDMODULECA_ORDERS_STATE'));

        foreach ($list_statuts as $statut => $value) {
            $statuts['os_' . $value['id_order_state']] = 0;
        }

        foreach ($conf_statuts as $conf) {
            $statuts['os_' . $conf] = 1;
        }

        $statuts['os_nbr_jours'] = Configuration::get('CDMODULECA_ORDERS_STATE_JOURS');

        return $statuts;
    }

    /**
     * Retourne la configuration des codes actions
     * @return array
     */
    private function getConfigCodeAction()
    {
        $code_action = array();
        $codes = $this->getAllCodesAction();
        foreach ($codes as $code => $value) {
            $code_action['ca_' . $value['id_code_action']] = $value['groupe'];
        }

        return $code_action;
    }

    /**
     * Retourne la configuration des employés de chaque group
     * @return array (id_group => value_parrain)
     */
    private function getConfigGroupsParrain()
    {
        $groups_parrain = array();
        $groups = $this->getGroupsParrain();
        foreach ($groups as $group => $value) {
            $groups_parrain['gp_' . $value['id_group']] = $value['id_employee'];
        }

        return $groups_parrain;
    }

    /**
     * Retourn tous les groupes avec leurs configuration
     * @return array (id_group, name, value_parrain)
     */
    public function getGroupsParrain()
    {
        $sql = 'SELECT `id_group`, `name`, `id_employee` FROM `' . _DB_PREFIX_ . 'group_lang` WHERE `id_lang` = "'
            . (int)$this->lang . '"';
        $groups = Db::getInstance()->executeS($sql);

        return $groups;
    }

    public function getConfigConfiguration()
    {
        $values = array(
            'co_prime_fichier' => Configuration::get('CDMODULECA_PRIME_FICHIER'),
            'co_prospects_jour' => Configuration::get('CDMODULECA_PROSPECTS_JOUR'),
            'co_prospects_heure' => Configuration::get('CDMODULECA_PROSPECTS_HEURE'),
            'co_prospects_jour_max' => Configuration::get('CDMODULECA_NBR_JOUR_MAX_PROSPECTS'),
            'co_prime_parrainage' => Configuration::get('CDMODULECA_PRIME_PARRAINAGE'),
            'co_seuil_prime_fichier' => Configuration::get('CDMODULECA_SEUIL_PRIME_FICHIER')
        );

        return $values;
    }

    /**
     * Mise à jour de la table ps_orders avec les codes action et les id_employee correspondant aux commandes
     * @return bool
     */
    private function updateOrdersTable()
    {
        $this->updateOrdersTableIdEmployee();
        $this->updateOrdersTableIdCodeAction();

        return true;
    }

    /**
     * Mise à jour de la table orders avec les d_employee
     */
    private function updateOrdersTableIdEmployee()
    {
        $reqCoachs = new DbQuery();
        $reqCoachs->select('DISTINCT coach, e.id_employee')
            ->from('orders')
            ->leftJoin('employee', 'e', 'e.lastname = coach');

        $listCoachs = Db::getInstance()->executeS($reqCoachs);

        foreach ($listCoachs as $coach) {
            if (!empty($coach['id_employee'])) {
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET `id_employee` = ' . (int)$coach['id_employee'] . '
            WHERE `coach` = "' . pSQL($coach['coach']) . '"';
                Db::getInstance()->execute($sql);
            }
        }
    }

    /**
     * Mise à jour de la table orders avec les id_code_action des groupe code action
     */
    private function updateOrdersTableIdCodeAction()
    {
        $listCodesAction = $this->getAllCodesAction();

        foreach ($listCodesAction as $code) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET `id_code_action` = ' . (int)$code['groupe'] . '
            WHERE  `code_action` = "' . pSQL($code['name']) . '"';
            Db::getInstance()->execute($sql);
        }
    }

    private function getAllCodesAction()
    {
        $sql = 'SELECT `id_code_action`, `name`, `description`, `groupe` FROM `' . _DB_PREFIX_ . 'code_action`';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    public function getCodeActionByName($name)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE `name` = "' . pSQL($name) . '"';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Intercepte la validation d'une commande pour ajouter l'id_code_action et l'id_employee lié à la commande
     * @param $params
     */
    public function hookActionValidateOrder($params)
    {
        $idOrder = Order::getOrderByCartId($this->context->cart->id);
        // Récupération du coach et du code action envoyé par le formulaire de commande
        $coach = Tools::getValue('coach');
        $code_action = Tools::getValue('order_code_action');

        if (!empty($coach) && !empty($code_action)) {
            // Correspondance entre le coach et le code action en id correspondant
            $id_coach = $this->getIdCoach($coach);
            $id_code_action = $this->getIdCodeAction($code_action);

            if (empty($id_code_action)) {
                $data = array(
                    'id_code_action' => '',
                    'name' => pSQL($code_action),
                    'description' => pSQL($code_action),
                    'groupe' => 2
                );

                Db::getInstance()->insert('code_action', $data);
            }

            $id_code_action = $this->getIdCodeAction($code_action);
            // Si il y a les id, on met à jour la commande passé avec les id coach et code_action
            if (!empty($id_coach) && !empty($id_code_action)) {
                $req = 'UPDATE `' . _DB_PREFIX_ . 'orders`
                    SET `id_employee` = ' . (int)$id_coach . ', `id_code_action` = ' . (int)$id_code_action . '
                    WHERE `id_order` = ' . (int)$idOrder;

                Db::getInstance()->execute($req);
            }
        }

        return true;
    }

    /**
     * fix payment by internet
     */
    public function hookActionOrderStatusUpdate()
    {
        $idOrder = Order::getOrderByCartId($this->context->cart->id);
        $order = CaTools::getOrderDetailsCoach($idOrder);

        if ((empty($order['id_code_action']) || empty($order['id_employee'])) &&
            (!empty($order['code_action']) && !empty($order['coach']))
        ) {
            // Correspondance entre le coach et le code action en id correspondant
            $id_coach = $this->getIdCoach($order['coach']);
            $id_code_action = $this->getIdCodeAction($order['code_action']);

            if (empty($id_code_action)) {
                $data = array(
                    'id_code_action' => '',
                    'name' => pSQL($order['code_action']),
                    'description' => pSQL($order['code_action']),
                    'groupe' => 2
                );

                Db::getInstance()->insert('code_action', $data);
            }

            $id_code_action = $this->getIdCodeAction($order['code_action']);

            // Si il y a les id, on met à jour la commande passé avec les id coach et code_action
            if (!empty($id_coach) && !empty($id_code_action)) {
                $req = 'UPDATE `' . _DB_PREFIX_ . 'orders`
                    SET `id_employee` = ' . (int)$id_coach . ', `id_code_action` = ' . (int)$id_code_action . '
                    WHERE `id_order` = ' . (int)$idOrder;

                Db::getInstance()->execute($req);
            }
        }
    }

    /**
     * Affiche le tableau de stat principal dans le menu Stats->L&Sens
     */
    protected function getData()
    {
        if ($this->viewAllCoachs[$this->context->employee->id_profile]) {
            $this->idFilterCoach = $this->context->cookie->cdmoculeca_id_filter_coach;
        } else {
            $this->idFilterCoach = $this->context->employee->id;
        }

        $this->idFilterCodeAction = $this->context->cookie->cdmoduleca_id_filter_code_action;
        $this->commandeValid = $this->context->cookie->cdmoculeca_filter_commande;

        $filterGroupe = ' LEFT JOIN ' . _DB_PREFIX_ . 'customer_group AS cg ON o.id_customer = cg.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'group_lang AS gl ON gl.id_group = cg.id_group';

        $idGroupEmployee = $this->getGroupeEmployee($this->idFilterCoach);

        $filterCoach = ($this->idFilterCoach != 0)
            ? ' AND ((gl.id_group = "' . $idGroupEmployee . '" AND gl.id_lang = "' . $this->lang . '")
                OR o.id_employee = ' . $this->idFilterCoach . ')'
            : '';

        $filterCodeAction = '';
        if ($this->idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.`id_code_action` != ' . (int)CaTools::getCodeActionByName('ABO');
            // Affiche les commandes avec le groupe du coach uniquement si il n'y a pas d filtre code action
            $filterCoach = ($this->idFilterCoach != 0) ? ' AND o.`id_employee` = ' . (int)$this->idFilterCoach . ' ' : '';
        } elseif ($this->idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.`id_code_action` = ' . (int)$this->idFilterCodeAction;
        }

        $filterValid = '';
        if ($this->commandeValid == 0) {
            $filterValid = ' AND o.`valid` = "0" ';
        } elseif ($this->commandeValid == 1) {
            $filterValid = ' AND o.`valid` = "1" ';
        }

        $this->query = '(
          SELECT SQL_CALC_FOUND_ROWS
          DISTINCT o.`id_order` AS id,
          "" AS avoir,
          "" AS impaye,
          "" AS ajustement,
          "" AS commentaire,
          gl.`name` AS groupe,
          CONCAT ( ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2), " €") AS hthp,
          (SELECT e.`lastname` FROM `' . _DB_PREFIX_ . 'employee` AS e WHERE o.`id_employee` = e.`id_employee`) AS id_employee,
          (SELECT UCASE(c.`lastname`) FROM `' . _DB_PREFIX_ . 'customer` AS c 
          WHERE o.`id_customer` = c.`id_customer`) AS id_customer,
          o.`date_add` AS date_add,
          o.`date_upd`,
          IF((o.`valid`) > 0, "", "Non") AS valid,
          (SELECT ca.`name` FROM `' . _DB_PREFIX_ . 'code_action` AS ca 
          WHERE o.`id_code_action` = ca.`id_code_action`) as CodeAction,
          (SELECT osl.`name` FROM `' . _DB_PREFIX_ . 'order_state_lang` AS osl 
          WHERE `id_lang` = "' . $this->lang . '" AND osl.`id_order_state` = o.`current_state` ) as current_state ,
          IF((SELECT so.`id_order` FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.`id_customer` = o.`id_customer`
          AND so.`id_order` < o.`id_order` LIMIT 1) > 0, "", "Oui") as new
				FROM `' . _DB_PREFIX_ . 'orders` AS o ';
        $this->query .= $filterGroupe;
        $this->query .= ' WHERE o.`date_add` BETWEEN ' . $this->getDate();
        $this->query .= $filterCoach;
        $this->query .= $filterCodeAction;
        $this->query .= ' AND o.`current_state` != 460';
        $this->query .= $filterValid;
        $this->query .= ' GROUP BY o.`id_order` ';
        $this->query .= ') UNION ( 
        SELECT os.`id_order` AS id,
        IF((os.`total_products_tax_excl`) != 0, CONCAT(ROUND(os.`total_products_tax_excl`, 2)," €"), "") AS avoir,
        "",
        "",
        "",
        "",
        "",
        (SELECT e.`lastname` FROM `' . _DB_PREFIX_ . 'employee` AS e WHERE o.`id_employee` = e.`id_employee`) AS id_employee,
          (SELECT UCASE(c.`lastname`) FROM `' . _DB_PREFIX_ . 'customer` AS c 
          WHERE o.`id_customer` = c.`id_customer`) AS id_customer,
        os.`date_add` AS date_add,
        "" ,
        "",
        "",
        (SELECT osl.`name` FROM `' . _DB_PREFIX_ . 'order_state_lang` AS osl 
          WHERE `id_lang` = "' . $this->lang . '" AND osl.`id_order_state` = o.`current_state` ) as current_state ,
        ""
        FROM `' . _DB_PREFIX_ . 'order_slip` AS os
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` AS o ON os.`id_order` = o.`id_order` ';
        $this->query .= $filterGroupe;
        $this->query .= ' WHERE os.`date_add` BETWEEN ' . $this->getDate();
        $this->query .= $filterCoach;
        $this->query .= 'AND o.current_state != 6';
        $this->query .= $filterCodeAction;
        $this->query .= ' GROUP BY o.`id_order` ';
        $this->query .= ') UNION (';
        $this->query .= 'SELECT 
        `id_order`,
        "",
        IF((`somme` != 0), CONCAT(`somme`," €"), "") AS somme,
        "",
        a.`commentaire`,
        "",
        "",
        e.`lastname`,
        "",
        a.`date_ajout_somme`,
        "","","","","" ';
        $this->query .= ' FROM `' . _DB_PREFIX_ . 'ajout_somme` AS a 
        LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
        WHERE `impaye` = 1
        AND `date_ajout_somme` BETWEEN ' . $this->getDate();
        $this->query .= ($this->idFilterCoach != 0)
            ? ' AND a.`id_employee` = ' . $this->idFilterCoach
            : '';
        $this->query .= ') UNION (';
        $this->query .= 'SELECT 
        `id_order`,
        "",
        "",
        IF((`somme` != 0), CONCAT(`somme`," €"), "") AS somme,
        a.`commentaire`,
        "",
        "",
        e.`lastname`,
        "",
        a.`date_ajout_somme`,
        "","","","","" ';
        $this->query .= ' FROM `' . _DB_PREFIX_ . 'ajout_somme` AS a 
        LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
        WHERE `impaye` IS NULL
        AND `date_ajout_somme` BETWEEN ' . $this->getDate();
        $this->query .= ($this->idFilterCoach != 0)
            ? ' AND a.`id_employee` = ' . $this->idFilterCoach
            : '';
        $this->query .= ' ORDER BY `date_ajout_somme` ASC';
        $this->query .= ')';


        if (Validate::IsName($this->_sort)) {
            $this->query .= ' ORDER BY `' . bqSQL($this->_sort) . '`';
            if (isset($this->_direction) && (Tools::strtoupper($this->_direction) == 'ASC' ||
                    Tools::strtoupper($this->_direction) == 'DESC')
            ) {
                $this->query .= ' ' . pSQL($this->_direction);
            }
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit)) {
            $this->query .= ' LIMIT ' . (int)$this->_start . ', ' . (int)$this->_limit;
        }

        $values = Db::getInstance()->executeS($this->query);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
    }

    private function getIdCoach($coach)
    {
        $req = new DbQuery();
        $req->select('id_employee')
            ->from('employee')
            ->where('lastname = "' . pSQL($coach) . '"');

        return Db::getInstance()->getValue($req);
    }

    private function getIdCodeAction($code_action)
    {
        $req = new DbQuery();
        $req->select('groupe')
            ->from('code_action')
            ->where('name = "' . pSQL($code_action) . '"');

        return Db::getInstance()->getValue($req);
    }

    public function getEmployees($active = 0, $id = null)
    {
        $sql = 'SELECT `id_employee`, `firstname`, `lastname`
			FROM `' . _DB_PREFIX_ . 'employee` ';
        $sql .= ($active == 'on') ? 'WHERE `active` = 1 ' : '';
        $sql .= ($id) ? ' WHERE `id_employee` = ' . (int)$id : '';
        $sql .= ' ORDER BY `id_employee` ASC';
        return Db::getInstance()->executeS($sql);
    }

    public function getGroupeEmployee($idFilterCoach)
    {
        $sql = 'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang` 
        WHERE `id_lang` = "' . (int)$this->lang . '" AND `id_employee` = ' . (int)$idFilterCoach;
        return Db::getInstance()->getValue($sql);
    }
}
