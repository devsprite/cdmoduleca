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


class GridClass extends Module
{
    protected $_employee;

    /** @var array of strings graph data */
    public $_values = array();

    /** @var integer total number of values * */
    protected $_totalCount = 0;

    /**@var string graph titles */
    protected $_title;

    /**@var integer start */
    protected $_start;

    /**@var integer limit */
    protected $_limit;

    /**@var string column name on which to sort */
    protected $_sort = null;

    /**@var string sort direction DESC/ASC */
    protected $_direction = null;

    /** @var ModuleGridEngine grid engine */
    protected $_render;

    public $data = array();

    public function setEmployee($id_employee)
    {
        $this->_employee = new Employee($id_employee);
    }

    public function setLang($id_lang)
    {
        $this->_id_lang = $id_lang;
    }

    public function create($render, $type, $width, $height, $start, $limit, $sort, $dir)
    {
        if (!Validate::isModuleName($render)) {
            die(Tools::displayError());
        }

        if (!Tools::file_exists_cache($file = _PS_ROOT_DIR_ . '/modules/' . $render . '/' . $render . '.php')) {
            die(Tools::displayError());
        }

        require_once($file);
        $this->_render = new $render($type);

        $this->_start = $start;
        $this->_limit = $limit;
        $this->_sort = $sort;
        $this->_direction = $dir;

        $this->getData($this->data);

        $this->_render->setTitle($this->_title);
        $this->_render->setSize($width, $height);
        $this->_render->setValues($this->_values);
        $this->_render->setTotalCount($this->_totalCount);
        $this->_render->setLimit($this->_start, $this->_limit);
    }

    public function render()
    {
        $this->_render->render();
    }

    public function engine($params)
    {

        if (!($render = Configuration::get('PS_STATS_GRID_RENDER'))) {
            return Tools::displayError('No grid engine selected');
        }

        if (!Validate::isModuleName($render)) {
            die(Tools::displayError());
        }
        if (!file_exists(_PS_ROOT_DIR_ . '/modules/' . $render . '/' . $render . '.php')) {
            return Tools::displayError('Grid engine selected is unavailable.');
        }


        $grider = 'grider.php?render=' . $render . '&module=cdmoduleca';

        $context = Context::getContext();
        $grider .= '&id_employee=' . (int)$context->employee->id;
        $grider .= '&id_lang=' . (int)$context->language->id;


        if (!isset($params['width']) || !Validate::IsUnsignedInt($params['width'])) {
            $params['width'] = 600;
        }
        if (!isset($params['height']) || !Validate::IsUnsignedInt($params['height'])) {
            $params['height'] = 920;
        }
        if (!isset($params['start']) || !Validate::IsUnsignedInt($params['start'])) {
            $params['start'] = 0;
        }
        if (!isset($params['limit']) || !Validate::IsUnsignedInt($params['limit'])) {
            $params['limit'] = 40;
        }

        $grider .= '&width=' . $params['width'];
        $grider .= '&height=' . $params['height'];
        if (isset($params['start']) && Validate::IsUnsignedInt($params['start'])) {
            $grider .= '&start=' . $params['start'];
        }
        if (isset($params['limit']) && Validate::IsUnsignedInt($params['limit'])) {
            $grider .= '&limit=' . $params['limit'];
        }
        if (isset($params['type']) && Validate::IsName($params['type'])) {
            $grider .= '&type=' . $params['type'];
        }
        if (isset($params['option']) && Validate::IsGenericName($params['option'])) {
            $grider .= '&option=' . $params['option'];
        }
        if (isset($params['sort']) && Validate::IsName($params['sort'])) {
            $grider .= '&sort=' . $params['sort'];
        }
        if (isset($params['dir']) && Validate::isSortDirection($params['dir'])) {
            $grider .= '&dir=' . $params['dir'];
        }

        require_once(_PS_ROOT_DIR_ . '/modules/' . $render . '/' . $render . '.php');
        return call_user_func(array($render, 'hookGridEngine'), $params, $grider);
    }

    public function csvExport($datas, $nameFile)
    {
        $this->_sort = $datas['defaultSortColumn'];
        $this->setLang(Context::getContext()->language->id);
        $this->getData($this->data);

        $layers = isset($datas['layers']) ? $datas['layers'] : 1;

        if (isset($datas['option'])) {
            $this->setOption($datas['option'], $layers);
        }
        $this->_csv = chr(239) . chr(187) . chr(191) ;
        if (count($datas['columns'])) {
            foreach ($datas['columns'] as $column) {
                $this->_csv .= $column['header'] . ';';
            }
            $this->_csv = rtrim($this->_csv, ';') . "\n";

            foreach ($this->_values as $value) {
                foreach ($datas['columns'] as $column) {
                    $this->_csv .= $value[$column['dataIndex']] . ';';
                }
                $this->_csv = rtrim(str_replace('.', ',', $this->_csv), ';') . "\n";
            }
        }
        $this->_displayCsv($nameFile);
    }

    protected function _displayCsv($nameFile)
    {
        if (ob_get_level() && ob_get_length() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nameFile . '.csv"');
        echo $this->_csv;
        exit;
    }

    public function getDate()
    {
        return ModuleGraph::getDateBetween($this->_employee);
    }

    public function getLang()
    {
        return $this->_id_lang;
    }

    /**
     * Utilisé pour générer le fichier export csv, identique à getData de cdmoduleca.php
     * @param $data
     */
    protected function getData($data)
    {
        $this->idFilterCoach = $this->context->cookie->cdmoculeca_id_filter_coach;
        $this->idFilterCodeAction = $this->context->cookie->cdmoduleca_id_filter_code_action;
        $this->commandeValid = $this->context->cookie->cdmoculeca_filter_commande;

        $filterGroupe = ' LEFT JOIN `' . _DB_PREFIX_ . 'customer_group` AS cg ON o.`id_customer` = cg.`id_customer`
                LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` AS gl ON gl.`id_group` = cg.`id_group`';

        $idGroupEmployee = $data['idGroupEmployee'];

        $filterCoach = ($data['idFilterCoach'] != 0)
            ? ' AND ((gl.`id_group` = "' . $idGroupEmployee . '" AND gl.`id_lang` = "' . $data['lang'] . '")
                OR o.`id_employee` = ' . $data['idFilterCoach'] . ')'
            : '';

        $filterCodeAction = '';
        if ($data['idFilterCodeAction'] == 99) {
            $filterCodeAction = ' AND o.`id_code_action` != ' . CaTools::getCodeActionByName('ABO');
            $filterCoach = ($data['idFilterCoach'] != 0) ? ' AND o.`id_employee` = ' . $data['idFilterCoach'] .' ':'';
        } elseif ($data['idFilterCodeAction'] != 0) {
            $filterCodeAction = ' AND o.`id_code_action` = ' . $data['idFilterCodeAction'];
        }

        $filterValid = '';
        if ($data['commandeValid'] == 0) {
            $filterValid = ' AND o.`valid` = "0" ';
        } elseif ($data['commandeValid'] == 1) {
            $filterValid = ' AND o.`valid` = "1" ';
        }

        $sql = '(
          SELECT SQL_CALC_FOUND_ROWS
          DISTINCT o.`id_order` AS id,
          "" AS avoir,
          "" AS impaye,
          "" AS ajustement,
          "" AS commentaire,
          gl.`name` AS groupe,
          ROUND(o.`total_products` - o.`total_discounts_tax_excl`,2) AS hthp,
          (SELECT e.`lastname` FROM `' . _DB_PREFIX_ . 'employee` AS e WHERE o.`id_employee` = e.`id_employee`) AS id_employee,
          (SELECT UCASE(c.`lastname`) FROM `' . _DB_PREFIX_ . 'customer` AS c 
          WHERE o.`id_customer` = c.`id_customer`) AS id_customer,
          o.`date_add` AS date_add,
          o.`date_upd`,
          IF((o.`valid`) > 0, "", "Non") AS valid,
          (SELECT ca.`name` FROM `' . _DB_PREFIX_ . 'code_action` AS ca 
          WHERE o.`id_code_action` = ca.`id_code_action`) as CodeAction,
          (SELECT osl.`name` FROM `' . _DB_PREFIX_ . 'order_state_lang` AS osl 
          WHERE `id_lang` = "' . $data['lang'] . '" AND osl.`id_order_state` = o.`current_state` ) as current_state ,
          IF((SELECT so.`id_order` FROM `' . _DB_PREFIX_ . 'orders` so WHERE so.`id_customer` = o.`id_customer`
          AND so.`id_order` < o.`id_order` LIMIT 1) > 0, "", "Oui") as new
                FROM `' . _DB_PREFIX_ . 'orders` AS o ';
        $sql .= $filterGroupe;
        $sql .= ' WHERE o.`date_add` BETWEEN ' . $data['date'];
        $sql .= $filterCoach;
        $sql .= $filterCodeAction;
        $sql .= ' AND o.`current_state` != 460';
        $sql .= $filterValid;
        $sql .= ' GROUP BY o.`id_order` ';
        $sql .= ') UNION ( 
        SELECT os.`id_order` AS id,
        IF((os.`total_products_tax_excl`) != 0, ROUND(os.`total_products_tax_excl`, 2), "") AS avoir,
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
          WHERE `id_lang` = "' . $data['lang'] . '" AND osl.`id_order_state` = o.`current_state` ) as current_state ,
        ""
        FROM `' . _DB_PREFIX_ . 'order_slip` AS os
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` AS o ON os.`id_order` = o.`id_order` ';
        $sql .= $filterGroupe;
        $sql .= ' WHERE os.`date_add` BETWEEN ' . $data['date'];
        $sql .= $filterCoach;
        $sql .= 'AND o.current_state != 6';
        $sql .= $filterCodeAction;
        $sql .= ' GROUP BY o.`id_order` ';
        $sql .= ') UNION (';
        $sql .= 'SELECT 
        `id_order`,
        "",
        IF((`somme` != 0), `somme`, "") AS somme,
        "",
        a.`commentaire`,
        "",
        "",
        e.`lastname`,
        "",
        a.`date_ajout_somme`,
        "","","","","" ';
        $sql .= ' FROM `' . _DB_PREFIX_ . 'ajout_somme` AS a 
        LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
        WHERE `impaye` = 1
        AND `date_ajout_somme` BETWEEN ' . $data['date'];
        $sql .= ($this->idFilterCoach != 0)
            ? ' AND a.`id_employee` = '. $this->idFilterCoach
            :'';
        $sql .= ') UNION (';
        $sql .= 'SELECT 
        `id_order`,
        "",
        "",
        IF((`somme` != 0), `somme`, "") AS somme,
        a.`commentaire`,
        "",
        "",
        e.`lastname`,
        "",
        a.`date_ajout_somme`,
        "","","","","" ';
        $sql .= ' FROM `' . _DB_PREFIX_ . 'ajout_somme` AS a 
        LEFT JOIN `' . _DB_PREFIX_ . 'employee` AS e ON a.`id_employee` = e.`id_employee`
        WHERE `impaye` IS NULL
        AND `date_ajout_somme` BETWEEN ' . $data['date'];
        $sql .= ($this->idFilterCoach != 0)
            ? ' AND a.`id_employee` = '. $this->idFilterCoach
            :'';
        $sql .=' ORDER BY `date_ajout_somme` ASC';
        $sql .= ')';



        if (Validate::IsName($this->_sort)) {
            $sql .= ' ORDER BY `' . bqSQL($this->_sort) . '`';
            if (isset($this->_direction) && (Tools::strtoupper($this->_direction) == 'ASC' ||
                    Tools::strtoupper($this->_direction) == 'DESC')
            ) {
                $sql .= ' ' . pSQL($this->_direction);
            }
        }

        if (($this->_start === 0 || Validate::IsUnsignedInt($this->_start)) && Validate::IsUnsignedInt($this->_limit)) {
            $sql .= ' LIMIT ' . (int)$this->_start . ', ' . (int)$this->_limit;
        }

        $values = Db::getInstance()->executeS($sql);

        $this->_values = $values;
        $this->_totalCount = Db::getInstance()->getValue('SELECT FOUND_ROWS()');
    }

}
