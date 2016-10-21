<?php
/**
 * 2007-2014 PrestaShop
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
    public $viewAllCoachs;
    public $idFilterCoach;
    public $idFilterCodeAction;
    public $employees_actif;
    public $commandeValid;
    public $lang;
    public $config = array(
        'CDMODULECA' => '1',
        'CDMODULECA_ORDERS_STATE' => '7',
        'CDMODULECA_ORDERS_STATE_JOURS' => '60'
    );

    public function __construct()
    {
        $this->name = 'cdmoduleca';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Dominique';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->lang = Configuration::get('PS_LANG_DEFAULT');
        parent::__construct();

        $this->displayName = $this->l('Module CA');
        $this->description = $this->l('Synthèse CA pour L et Sens');
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
        $this->limit = 40;
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
                'align' => 'center'
            ),
            array(
                'id' => 'valid',
                'header' => $this->l('Valide'),
                'dataIndex' => 'valid',
                'align' => 'center'
            ),
            array(
                'id' => 'id_customer',
                'header' => $this->l('Client'),
                'dataIndex' => 'id_customer',
                'align' => 'left'
            ),
            array(
                'id' => 'hthp',
                'header' => $this->l('ht-hp'),
                'dataIndex' => 'hthp',
                'align' => 'right',
            ),
            array(
                'id' => 'id_employee',
                'header' => $this->l('Coach'),
                'dataIndex' => 'id_employee',
                'align' => 'center'
            ),
            array(
                'id' => 'groupe',
                'header' => $this->l('Groupe'),
                'dataIndex' => 'groupe',
                'align' => 'center'
            ),
            array(
                'id' => 'current_state',
                'header' => $this->l('Etat Commande'),
                'dataIndex' => 'current_state',
                'align' => 'left'
            ),
            array(
                'id' => 'CodeAction',
                'header' => $this->l('Code action'),
                'dataIndex' => 'CodeAction',
                'align' => 'left'
            ),
            array(
                'id' => 'new',
                'header' => $this->l('Nouveau Client'),
                'dataIndex' => 'new',
                'align' => 'center'
            ),
            array(
                'id' => 'date_add',
                'header' => $this->l('date add'),
                'dataIndex' => 'date_add',
                'align' => 'center'
            ),

        );

        if ($this->isEnabledForShopContext()) {
            $this->setIdFilterCoach();
            $this->setIdFilterCodeAction();
            $this->setFilterCommandeValid();
            $this->AjoutSomme();
            $this->AjoutObjectif();
        }
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->createTabs() ||
            !$this->createTableProspectAttribue() ||
            !$this->createTableProspect() ||
            !$this->createTableObjectifCoach() ||
            !$this->createTableAjoutSomme() ||
            !$this->alterGroupLangTable() ||
            !$this->installConfigGroupes() ||
            !$this->alterOrderTable() ||
            !$this->createCodeActionTable() ||
            !$this->updateOrdersTable() ||
            !$this->installConfig() ||
            !$this->registerHook('AdminStatsModules') ||
            !$this->registerHook('ActionValidateOrder')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (
            !$this->eraseTabs() ||
            !$this->eraseTableProspectAttribue() ||
            !$this->eraseTableProspect() ||
            !$this->eraseTableObjectifCoach() ||
            !$this->eraseTableAjoutSomme() ||
            !$this->removeCodeActionTable() ||
            !$this->alterGroupLangTable('remove') ||
            !$this->alterOrderTable('remove') ||
            !$this->eraseConfig() ||
            !parent::uninstall()
        ) {
            return false;
        }

        return true;
    }

    private function createTabs()
    {
        $tab = new Tab();
        $tab->active = 1;
        $names = array(1=>'Prospects', 'Prospects');
        foreach (Language::getLanguages() as $language)
        {
            $tab->name[$language['id_lang']] = isset($names[$language['id_lang']]) ? $names[$language['id_lang']] : $names[1];
        }
        $tab->class_name = 'AdminProspects';
        $tab->module = $this->name;
        $tab->id_parent = Tab::getIdFromClassName('AdminCustomers');

        return (bool)$tab->add();
    }

    private function eraseTabs()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminProspects');
        if($id_tab)
        {
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
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'prospect_attribue` (
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
        return Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'prospect_attribue`');
    }

    /**
     * Table pour le suivi des prospect attribué au employes
     * @return bool
     */
    private function createTableProspect()
    {
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'prospect` (
        `id_prospect` INT(12) NOT NULL AUTO_INCREMENT,
        `id_customer` INT(12) NOT NULL,
        `id_prospect_attribue` INT(12) NOT NULL,
        `traite` VARCHAR(64) NULL,
        `injoignable` VARCHAR(64) NULL,
        `contacte` VARCHAR(64) NULL,
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
        return Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'prospect`');
    }

    /**
     * @return bool Table permettant l'ajout d'un objectif pour un employee
     */
    private function createTableObjectifCoach()
    {
        $sql = "CREATE TABLE `" . _DB_PREFIX_ . "objectif_coach` (
        `id_objectif_coach` INT (12) NOT NULL AUTO_INCREMENT,
        `somme` DECIMAL (8,2) NULL,
        `commentaire` VARCHAR(255) NULL,
        `id_employee` INT (12),
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
        if (!Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'objectif_coach`')) {
            return false;
        }
        return true;
    }

    /**
     * @return bool Table permettant l'ajout ou la déduction d'une prime pour un employee
     */
    private function createTableAjoutSomme()
    {
        $sql = "CREATE TABLE `" . _DB_PREFIX_ . "ajout_somme` (
        `id_ajout_somme` INT (12) NOT NULL AUTO_INCREMENT,
        `somme` DECIMAL (8,2) NULL,
        `commentaire` VARCHAR(255) NULL,
        `id_employee` INT (12),
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
        if (!Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'ajout_somme`')) {
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
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'code_action` (
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
        if (!Db::getInstance()->execute('DROP TABLE `' . _DB_PREFIX_ . 'code_action`')) {
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
                'id_code_action' => $c,
                'name' => $key,
                'description' => $value[0],
                'groupe' => $value[1]
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
            Db::getInstance()->update('group_lang', array('id_employee' => $value), 'id_group = ' . $groupe
                . ' AND id_lang = "' . $this->lang . '"');
        }

        return true;
    }

    /**
     * permet de lié une commande avec un code action et un employee
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

    public function getContent()
    {
        $this->postProcess();
        $this->displayForm();

        return $this->html;
    }

    private function postProcess()
    {
        $error = '';
        // Mise à jour de la configuration des groupes lié au employés
        if (Tools::isSubmit('submitUpdateGroups')) {
            $groups = $this->getGroupsParrain();
            foreach ($groups as $group) {
                if (!Db::getInstance()->update('group_lang',
                    array('id_employee' => intval(str_replace('gp_', '', Tools::getValue('gp_' . $group['id_group'])))),
                    'id_lang = "' . $this->lang . '" AND id_group = ' . str_replace('gp_', '', $group['id_group']))
                ) {
                    $error .= $this->l('Erreur lors de la mise à jour des groupes');
                }
            }
            // Mise à jour de la configuration des code action, permet de regrouper les codes actions entre eux
        } elseif (Tools::isSubmit('submitUpdateCodeAction')) {
            $codes_action = $this->getAllCodesAction();
            foreach ($codes_action as $code) {
                if (Db::getInstance()->update('code_action',
                    array('groupe' => str_replace('ca_', '', Tools::getValue('ca_' . $code['id_code_action']))),
                    'id_code_action = ' . str_replace('ca_', '', $code['id_code_action']))
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
                    $statuts[] = $statut['id_order_state'];
                }
            }
            $confStatus = implode(',', $statuts);
            Configuration::updateValue('CDMODULECA_ORDERS_STATE', $confStatus);
            Configuration::updateValue('CDMODULECA_ORDERS_STATE_JOURS', (int)Tools::getValue('os_nbr_jours'));
        }

        if ($error) {
            $this->html .= $this->displayError($error);
        } else {
//            $this->html .= $this->displayConfirmation($this->l('Groupement des codes action mis à jour.'));

        }
    }

    private function displayForm()
    {
        $this->html .= $this->generateFormCodeAction();
        $this->html .= $this->generateFormStatutsCommande();
        $this->html .= $this->generateFormGroupeParrain();
        $this->html .= $this->display(__FILE__, 'configuration.tpl');
    }

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
     * retourne la configuration des status lié au commandes
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
        $sql = 'SELECT id_group, name, id_employee FROM `' . _DB_PREFIX_ . 'group_lang` WHERE id_lang = "'
            . $this->lang . '"';
        $groups = Db::getInstance()->executeS($sql);

        return $groups;
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
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET id_employee = ' . $coach['id_employee'] . '
            WHERE coach = "' . $coach['coach'] . '"';
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
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET id_code_action = ' . $code['groupe'] . '
            WHERE  code_action = "' . $code['name'] . '"';
            Db::getInstance()->execute($sql);
        }
    }

    private function getAllCodesAction()
    {
        $sql = 'SELECT id_code_action, name, description, groupe FROM `' . _DB_PREFIX_ . 'code_action`';
        $req = Db::getInstance()->executeS($sql);

        return $req;
    }

    private function getCodeAction($id)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE id_code_action = ' . (intval($id));

        return Db::getInstance()->getRow($sql);
    }

    private function getCodeActionByName($name)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'code_action` WHERE name = "' . pSQL($name) . '"';

        return Db::getInstance()->getValue($sql);
    }

    private function getAllGroupeCodesAction()
    {
        $sql = 'SELECT DISTINCT groupe FROM `' . _DB_PREFIX_ . 'code_action`
        ';
        $groupes = Db::getInstance()->executeS($sql);

        $listGroupes = array();
        foreach ($groupes as $groupe) {
            $listGroupes[] = $this->getCodeAction($groupe['groupe']);
        }

        return $listGroupes;
    }

    // ***************** Fin de la partie installation et configuration du module ********/
    // ***************** Début de la page stat *******************************************/

    public function hookAdminStatsModules($params)
    {
        $this->context->controller->addCSS(_PS_MODULE_DIR_ . 'cdmoduleca/views/css/statscdmoduleca.css');
        $engine_params = array(
            'id' => 'id_order',
            'title' => $this->displayName,
            'columns' => $this->columns,
            'defaultSortColumn' => $this->default_sort_column,
            'defaultSortDirection' => $this->default_sort_direction,
            'emptyMessage' => $this->empty_message,
            'pagingMessage' => $this->paging_message,
            'limit' => $this->limit,
        );

        if (Tools::getValue('export')) {
            $this->csvExport($engine_params);
        }

        $this->smarty->assign(array(
            'displayName' => $this->displayName,
            'CSVExport' => $this->l('CSV Export'),
            'CSVLink' => Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1')
        ));

        $this->html .= $this->display(__FILE__, 'headerstats.tpl');
        $this->html .= $this->syntheseCoachs();
        $this->html .= $this->engine($engine_params);
        $this->html .= $this->display(__FILE__, 'footerstats.tpl');

        return $this->html;
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
     * Enregistrement de la configuration du filtre coach dans un cookie
     */
    private function setIdFilterCoach()
    {
        $this->idFilterCoach = (int)$this->context->employee->id;
        $this->employees_actif = 1;
        if ($this->viewAllCoachs[$this->context->employee->id_profile]) {
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
     * Enregistre, modifie, oou efface une ligne de la table ajout_somme
     */
    private function AjoutSomme()
    {
        if ($this->viewAllCoachs[$this->context->employee->id_profile]) {
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
                        unset($_POST['as_date_add']);
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
                $as = $this->getAjoutSommeById((int)Tools::getValue('id_as'));
                $_POST['as_id_employee'] = $as['id_employee'];
                $_POST['as_somme'] = $as['somme'];
                $_POST['as_commentaire'] = $as['commentaire'];
                $_POST['as_date_add'] = $as['date_add'];
                $_POST['as_id'] = $as['id_ajout_somme'];
            }
        }

        $ajoutSommes = $this->getAjoutSomme($this->idFilterCoach);
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
        if ($this->viewAllCoachs[$this->context->employee->id_profile]) {
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


                if (!$this->errors) {
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
                    if (!$this->errors) {
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
                $oc = $this->getObjectifById((int)Tools::getValue('id_oc'));
                $_POST['oc_id_employee'] = $oc['id_employee'];
                $_POST['oc_somme'] = $oc['somme'];
                $_POST['oc_commentaire'] = $oc['commentaire'];
                $_POST['oc_date_start'] = $oc['date_start'];
                $_POST['oc_date_end'] = $oc['date_end'];
                $_POST['oc_id'] = $oc['id_objectif_coach'];
            }
        }

        $objectifCoachs = $this->getObjectifCoachs($this->idFilterCoach);
        $objectifs = $this->isObjectifAteint($objectifCoachs);
        $this->smarty->assign(array(
            'objectifCoachs' => $objectifs
        ));
        $this->smarty->assign(array(
            'errors' => $this->errors,
            'confirmation' => $this->confirmation,
        ));
    }

    private function getAjoutSommeById($id)
    {
        $sql = 'SELECT * FROM ps_ajout_somme WHERE id_ajout_somme = ' . $id;

        return Db::getInstance()->getRow($sql);
    }

    private function getAjoutSomme($id_employee)
    {
        $sql = 'SELECT id_ajout_somme, somme, commentaire, a.id_employee, date_add, lastname
                FROM `ps_ajout_somme` AS a
                LEFT JOIN `ps_employee` AS e ON a.id_employee = e.id_employee
                WHERE date_add BETWEEN ' . $this->getDate();

        if ($id_employee != 0) {
            $sql .= ' AND a.id_employee = ' . (int)$id_employee;
        }

        return Db::getInstance()->executeS($sql);
    }

    private function getObjectifById($id)
    {
        $sql = 'SELECT * FROM ps_objectif_coach WHERE id_objectif_coach = ' . $id;

        return Db::getInstance()->getRow($sql);
    }

    private function getObjectifCoachs($id_employee)
    {
        $sql = 'SELECT id_objectif_coach, somme, commentaire, a.id_employee, date_start, date_end, lastname
                FROM `ps_objectif_coach` AS a
                LEFT JOIN `ps_employee` AS e ON a.id_employee = e.id_employee
                WHERE date_start BETWEEN ' . $this->getDate();

        if ($id_employee != 0) {
            $sql .= ' AND a.id_employee = ' . (int)$id_employee;
        }

        $sql .= ' ORDER BY id_employee ASC, date_start ASC';

        return Db::getInstance()->executeS($sql);
    }

    private function isObjectifAteint($objectifCoachs)
    {
        if ($objectifCoachs) {

            foreach ($objectifCoachs as $objectifCoach => $objectif) {
                $dateBetween = '"' . $objectif['date_start'] . '" AND "' . $objectif['date_end'] . '"';

                $caCoach = $this->getCaCoachsTotal($objectif['id_employee'], 0, $dateBetween);
                $p = round(((100 * $caCoach) / $objectif['somme']), 2);
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

    /**
     * Intercepte la validation d'une commande pour ajouter l'id_code_action et l'id_employee lié à la commande
     * @param $params
     */
    public function hookActionValidateOrder($params)
    {
        $employee = (isset($this->context->employee->id)) ? $this->context->employee->id : false;
        if ($employee) {
            $idOrder = Order::getOrderByCartId($this->context->cart->id);
            $reqOrder = new DbQuery();
            $reqOrder->select('id_order, coach, code_action')
                ->from('orders')
                ->where('id_order = ' . $idOrder);
            $order = Db::getInstance()->getRow($reqOrder);

            if (!empty($order)) {
                $id_coach = $this->getIdCoach($order['coach']);
                $id_code_action = $this->getIdCodeAction($order['code_action']);

                if (!empty($id_coach) && !empty($id_code_action)) {
                    $req = 'UPDATE `' . _DB_PREFIX_ . 'orders`
                    SET id_employee = ' . $id_coach . ', id_code_action = ' . $id_code_action . ' 
                    WHERE id_order = ' . $order['id_order'];

                    Db::getInstance()->execute($req);
                }
            }
        }

        return true;
    }

    /**
     * Affiche le tableau de stat principal
     */
    protected function getData()
    {
        $filterGroupe = ' LEFT JOIN ps_customer_group AS cg ON o.id_customer = cg.id_customer 
                LEFT JOIN ps_group_lang AS gl ON gl.id_group = cg.id_group';

        $idGroupEmployee = $this->getGroupeEmployee($this->idFilterCoach);

        $filterCoach = ($this->idFilterCoach != 0)
            ? ' AND ((gl.id_group = "' . $idGroupEmployee . '" AND gl.id_lang = "' . $this->lang . '")
                OR o.id_employee = ' . $this->idFilterCoach . ')'
            : '';

        $filterCodeAction = '';
        if ($this->idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.id_code_action != ' . $this->getCodeActionByName('ABO');
        } elseif ($this->idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.id_code_action = ' . $this->idFilterCodeAction;
        }

        $filterValid = '';
        if ($this->commandeValid == 0) {
            $filterValid = ' AND o.valid = "0" ';
        } elseif ($this->commandeValid == 1) {
            $filterValid = ' AND o.valid = "1" ';
        }

        $this->query = '
          SELECT SQL_CALC_FOUND_ROWS 
          DISTINCT o.id_order AS id,
          gl.name AS groupe,
          CONCAT ( ROUND(o.total_products - o.total_discounts_tax_excl,2), " €") AS hthp,
          (SELECT e.lastname FROM ps_employee AS e WHERE o.id_employee = e.id_employee) AS id_employee,
          (SELECT UCASE(c.lastname) FROM ps_customer AS c WHERE o.id_customer = c.id_customer) AS id_customer,
          date_add,
          date_upd,
          IF((o.valid) > 0, "", "Non") AS valid,  
          (SELECT ca.name FROM ps_code_action AS ca WHERE o.id_code_action = ca.id_code_action) as CodeAction,
          (SELECT osl.name FROM ps_order_state_lang AS osl WHERE id_lang = "' . $this->lang . '" AND osl.id_order_state = o.current_state ) as current_state ,
          IF((SELECT so.id_order FROM `ps_orders` so WHERE so.id_customer = o.id_customer 
          AND so.id_order < o.id_order LIMIT 1) > 0, "", "Oui") as new
				FROM ' . _DB_PREFIX_ . 'orders AS o ';
        $this->query .= $filterGroupe;
        $this->query .= ' WHERE date_add BETWEEN ' . $this->getDate();
        $this->query .= $filterCoach;
        $this->query .= $filterCodeAction;
        $this->query .= $filterValid;
        $this->query .= ' GROUP BY o.id_order ';


        if (Validate::IsName($this->_sort)) {
            $this->query .= ' ORDER BY `' . bqSQL($this->_sort) . '`';
            if (isset($this->_direction) && (Tools::strtoupper($this->_direction) == 'ASC' || Tools::strtoupper($this->_direction) == 'DESC'))
                $this->query .= ' ' . pSQL($this->_direction);
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit))
            $this->query .= ' LIMIT ' . (int)$this->_start . ', ' . (int)$this->_limit;

        $values = Db::getInstance()->executeS($this->query);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
    }

    private function getIdCoach($coach)
    {
        $req = new DbQuery();
        $req->select('id_employee')
            ->from('employee')
            ->where('lastname = ' . $coach);

        return Db::getInstance()->getValue($req);
    }

    private function getIdCodeAction($code_action)
    {
        $req = new DbQuery();
        $req->select('id_code_action')
            ->from('code_action')
            ->where('name = ' . $code_action);

        return Db::getInstance()->getValue($req);
    }

    /**
     * Appel des différrentes partie de la page stats
     * @return string
     */
    private function syntheseCoachs()
    {
        $html = $this->display(__FILE__, 'synthesecoachs/synthesecoachsheader.tpl');
        $html .= $this->syntheseCoachsFilter();
        $html .= $this->syntheseCoachsContent();
        $html .= $this->syntheseCoachsTable();
        $html .= $this->display(__FILE__, 'synthesecoachs/synthesecoachsfooter.tpl');
        return $html;
    }

    private function syntheseCoachsContent()
    {
        $this->syntheseCoachsContentGetData();
        return $this->display(__FILE__, 'synthesecoachs/synthesecoachscontent.tpl');
    }

    private function syntheseCoachsContentGetData()
    {
        $this->smarty->assign(array(
            'caCoachsTotal' => $this->getCaCoachsTotal(0, $this->idFilterCodeAction),
            'caCoach' => $this->getCaCoachsTotal($this->idFilterCoach, $this->idFilterCodeAction),
            'caFidTotal' => $this->getCaDejaInscrit(0),
            'caFidCoach' => $this->getCaDejaInscrit($this->idFilterCoach),
            'caDeduitTotal' => $this->getCaDeduit(),
            'caDeduitCoach' => $this->getCaDeduit($this->idFilterCoach),
            'caDeduitJours' => (int)Configuration::get('CDMODULECA_ORDERS_STATE_JOURS'),
            'caTotalNbrCommandes' => $this->getNumberCommande(0, $this->idFilterCodeAction, array(460, 443)),
            'caCoachNbrCommandes' => $this->getNumberCommande($this->idFilterCoach, $this->idFilterCodeAction, array(460, 443)),

            'caTotal' => $this->getCaCoachsTotal(0, 0),
            'caTotalCoach' => $this->getCaCoachsTotal($this->idFilterCoach, 0),
            'coach' => new Employee($this->idFilterCoach),
            'filterCodeAction' => $this->getCodeAction($this->idFilterCodeAction),
        ));
    }

    private function getCaCoachsTotal($idCoach = 0, $idCodeAction = 0, $dateBetween = false)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . $idCoach : '';

        $filterCodeAction = '';
        if ($this->idFilterCodeAction == 99) {
            $filterCodeAction = ' AND o.id_code_action != ' . $this->getCodeActionByName('ABO');
        } elseif ($this->idFilterCodeAction != 0) {
            $filterCodeAction = ' AND o.id_code_action = ' . $this->idFilterCodeAction;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS 
                if(SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) < 
                0 , 0, SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2))) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ';
        $sql .= ($dateBetween) ? $dateBetween : $this->getDate();
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;

        return Db::getInstance()->getValue($sql);
    }

    private function getNumberCommande($idCoach = 0, $idCodeAction = 0, $current_state = null)
    {
        $filterCoach = ($idCoach != 0)
            ? ' AND id_employee = ' . $idCoach : '';

        $filterCodeAction = ($idCodeAction != 0)
            ? ' AND id_code_action = ' . $idCodeAction : '';
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
        $sql .= ' AND date_add BETWEEN ' . $this->getDate();

        Db::getInstance()->executeS($sql);
        $nbr = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
        $nbr = ($nbr) ? $nbr : '';

        return $nbr;
    }

    private function syntheseCoachsFilter()
    {
        $linkFilterCoachs = AdminController::$currentIndex . '&module=' . $this->name
            . '&token=' . Tools::getValue('token');
        $this->smarty->assign(array(
            'linkFilter' => $linkFilterCoachs,
        ));

        $this->syntheseCoachsFilterCoach();
        $this->syntheseCoachsFilterCodeAction();
        return $this->display(__FILE__, 'synthesecoachs/synthesecoachsfilter.tpl');

    }

    private function syntheseCoachsFilterCoach()
    {
        $idProfil = $this->context->employee->id_profile;
        $commandeActive = array(
            array('key' => 'Non', 'value' => '0'),
            array('key' => 'Oui', 'value' => '1'),
            array('key' => 'Tout', 'value' => '2'));

        if ($this->viewAllCoachs[$idProfil]) {
            $listCoaches = $this->getEmployees($this->employees_actif);
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
        $listCodesAction = $this->getAllGroupeCodesAction();
        $listCodesAction[] = array(
            'id_code_action' => '0',
            'name' => 'Tous les codes'
        );

        $listCodesAction[] = array(
            'id_code_action' => '99',
            'name' => 'Tous les codes sauf ABO'
        );
        $this->context->smarty->assign(array(
            'codesAction' => $listCodesAction,
            'filterCodeAction' => $this->idFilterCodeAction
        ));
    }

    private function getCaDejaInscrit($idFilterCoach = 0)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . $idFilterCoach : '';

        $sql = 'SELECT ROUND(o.total_products - o.total_discounts_tax_excl,2) AS total,
                IF((SELECT so.id_order FROM `ps_orders` so WHERE so.id_customer = o.id_customer 
                AND so.id_order < o.id_order LIMIT 1) > 0, 1, 0) as notNew
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1';
        $sql .= $filterCoach;
        $sql .= ' AND date_add BETWEEN ' . $this->getDate();
        $caFID = Db::getInstance()->executeS($sql);

        $total = '';
        foreach ($caFID as $ca) {
            $total += ($ca['notNew']) ? $ca['total'] : 0;
        }

        return $total;
    }

    private function getCaDeduit($idFilterCoach = 0)
    {
        $listStatuts = explode(',', Configuration::get('CDMODULECA_ORDERS_STATE'));
        $sqlStatuts = ' AND ( ';
        foreach ($listStatuts as $statut) {
            $sqlStatuts .= ' current_state = ' . $statut . ' OR ';
        }
        $sqlStatuts = substr($sqlStatuts, 0, -3) . ')';

        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . $idFilterCoach : '';

        $sql = 'SELECT SUM(ROUND(o.total_products - o.total_discounts_tax_excl,2)) as total
                FROM ' . _DB_PREFIX_ . 'orders AS o';
        $sql .= ' WHERE date_add BETWEEN ' . $this->getDateCaDeduit();
        $sql .= $filterCoach;
        $sql .= $sqlStatuts;

        return Db::getInstance()->getValue($sql);
    }

    private function getDateCaDeduit()
    {
        $d = $this->getDate();
        $days = Configuration::get('CDMODULECA_ORDERS_STATE_JOURS');
        $d_start = "'" . date('Y-m-d H:i:s', strtotime(substr($d, 2, 19) . ' - ' . $days . ' days')) . "'";
        $d_end = "'" . date('Y-m-d H:i:s', strtotime(substr($d, 28, 19) . ' - ' . $days . ' days')) . "'";

        return $d_start . ' AND ' . $d_end;
    }

    public function getEmployees($active = 0, $id = null)
    {
        $sql = 'SELECT `id_employee`, `firstname`, `lastname`
			FROM `' . _DB_PREFIX_ . 'employee` ';
        $sql .= ($active == 'on') ? 'WHERE active = 1 ' : '';
        $sql .= ($id) ? ' WHERE id_employee = ' . $id : '';
        $sql .= ' ORDER BY `id_employee` ASC';
        return Db::getInstance()->executeS($sql);
    }

    private function syntheseCoachsTable()
    {
        $employees = $this->getEmployees(1, $this->context->employee->id);
        if ($this->viewAllCoachs[$this->context->employee->id_profile]) {
            $employees = $this->getEmployees($this->employees_actif);
        }

        $datasEmployees = array();
        foreach ($employees as $employee) {

            if (!empty($this->getCaCoachsTotal($employee['id_employee'], 0))) {

                $datasEmployees[$employee['id_employee']]['lastname'] = $employee['lastname'];
                $datasEmployees[$employee['id_employee']]['firstname'] = $employee['firstname'];

                $datasEmployees[$employee['id_employee']]['caTotal'] =
                    $this->getCaCoachsTotal($employee['id_employee'], 0);

                $datasEmployees[$employee['id_employee']]['caDejaInscrit'] =
                    $this->getCaDejaInscrit($employee['id_employee']);

                $datasEmployees[$employee['id_employee']]['CaProsp'] =
                    $this->caProsp($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['PourcCaProspect'] =
                    $this->PourcCaProspect($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['PourcCaFID'] =
                    $this->PourcCaFID($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['NbrCommandes'] =
                    $this->getNumberCommande($employee['id_employee'], null, array(460, 443));

                $datasEmployees[$employee['id_employee']]['panierMoyen'] =
                    $this->getPanierMoyen($datasEmployees[$employee['id_employee']]);

                $datasEmployees[$employee['id_employee']]['nbrVenteAbo'] =
                    $this->getNbrVentes($employee['id_employee'], 'ABO');

                $datasEmployees[$employee['id_employee']]['nbrVenteProsp'] =
                    $this->getNbrVentes($employee['id_employee'], 'Prosp');

                $datasEmployees[$employee['id_employee']]['nbrVenteFid'] =
                    $this->getNbrVentes($employee['id_employee'], 'FID');

                $datasEmployees[$employee['id_employee']]['nbrVentePar'] =
                    $this->getNbrVentes($employee['id_employee'], 'PAR');

                $datasEmployees[$employee['id_employee']]['nbrVenteReact'] =
                    $this->getNbrVentes($employee['id_employee'], 'REACT+4M');

                $datasEmployees[$employee['id_employee']]['nbrVenteCont'] =
                    $this->getNbrVentes($employee['id_employee'], 'CONT ENTR');

                $datasEmployees[$employee['id_employee']]['nbrVenteGrAbo'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462));

                $n = $this->getNbrGrVentes($employee['id_employee'], 'ABO', array(444, 462), true);
                $totalVenteGrAbo = ($n)?($n/100)*10:''; // Calcul de la prime 10 % sur la vente des abos
                $datasEmployees[$employee['id_employee']]['totalVenteGrAbo'] = $totalVenteGrAbo;

                $datasEmployees[$employee['id_employee']]['nbrVenteGrDesaAbo'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'ABO', array(440, 453, null, false, 0));

                $datasEmployees[$employee['id_employee']]['nbrVenteGrFid'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'FID');

                $datasEmployees[$employee['id_employee']]['totalVenteGrFid'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'FID', null, true);

                $datasEmployees[$employee['id_employee']]['nbrVenteGrProsp'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'PROSP');

                $datasEmployees[$employee['id_employee']]['totalVenteGrProsp'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'PROSP', null, true);

                $datasEmployees[$employee['id_employee']]['nbrVenteGrPar'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'PAR');

                $datasEmployees[$employee['id_employee']]['totalVenteGrPar'] =
                    $this->getNbrGrVentes($employee['id_employee'], 'PAR', null, true);
            }

        }

        $this->smarty->assign(array(
            'datasEmployees' => $datasEmployees,
            'dateRequete' => $this->getDate()
        ));

        return $this->display(__FILE__, 'synthesecoachs/synthesecoachstable.tpl');
    }


    private function getNbrGrVentes($idFilterCoach = 0, $code_action = null, $current_state = null, $totalMoney = false, $valid = false)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? " AND e . id_employee = '" . $idFilterCoach . "'" : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = $this->getCodeActionByName($code_action);
            $sql_code_action = " AND o . id_code_action = '" . $code_action . "'";
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
            LEFT JOIN ps_group_lang as gl ON cg . id_group = gl . id_group AND gl.id_lang = '" . $this->lang . "'
            LEFT JOIN ps_employee as e ON gl . id_employee = e . id_employee";
        $sql .= ' WHERE o.date_add BETWEEN ' . $this->getDate();
        $sql .= ($valid) ? ' AND o.valid = 1 ' : '';
        $sql .= $filterCoach;
        $sql .= $sql_code_action;
        $sql .= $filter_current_state;

        $nbrGrVentes = Db::getInstance()->getValue($sql);
        $nbrRows = $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

        return ($totalMoney) ? $nbrGrVentes : $nbrRows;
    }


    private function getNbrVentes($idFilterCoach = 0, $code_action = null)
    {
        $filterCoach = ($idFilterCoach != 0)
            ? ' AND id_employee = ' . $idFilterCoach : '';

        $sql_code_action = '';
        if ($code_action) {
            $code_action = $this->getCodeActionByName($code_action);
            $sql_code_action = ' AND id_code_action = "' . $code_action . '" ';
        }


        $sql = 'SELECT SQL_CALC_FOUND_ROWS id_order
				FROM ' . _DB_PREFIX_ . 'orders AS o
				WHERE valid = 1 ';
        $sql .= $sql_code_action;
        $sql .= $filterCoach;
        $sql .= ' AND date_add BETWEEN ' . $this->getDate();
        $nbrVenteFID = Db::getInstance()->executeS($sql);


        $nbrRows = $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');

        return ($nbrRows) ? $nbrRows : ''; // ($nbrVenteFID) ? $nbrVenteFID : '';
    }

    private function getPanierMoyen($data)
    {
        if ($data['NbrCommandes'] != 0) {
            return $data['caTotal'] / $data['NbrCommandes'];
        }
        return '';
    }

    private function caProsp($data)
    {
        return ($data['caTotal']) ? $data['caTotal'] - $data['caDejaInscrit'] : '';
    }

    private function PourcCaProspect($data)
    {
        if ($data['caTotal'] != 0) {
            return number_format(($data['CaProsp'] * 100) / $data['caTotal'], 2) . ' %';
        }
        return '';
    }

    private function PourcCaFID($data)
    {
        if ($data['caTotal'] != 0) {
            return number_format(($data['caDejaInscrit'] * 100) / $data['caTotal'], 2) . ' %';
        }
        return '';
    }

    public function getGroupeEmployee($idFilterCoach)
    {
        $sql = 'SELECT id_group FROM ps_group_lang WHERE id_lang = "' . $this->lang . '" AND id_employee = ' . (int)$idFilterCoach;
        return Db::getInstance()->getValue($sql);
    }
}
