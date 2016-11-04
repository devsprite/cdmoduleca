<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http:* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http:*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http:*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__) . '/../../../modules/cdmoduleca/classes/ProspectClass.php');
require_once(dirname(__FILE__) . '/../../../modules/cdmoduleca/classes/CaTools.php');

class AdminCustomersController extends AdminCustomersControllerCore
{
    /*
    * module: keyyo
    * date: 2016-08-16 17:17:59
    * version: 1.0.0
    */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->required_database = true;
        $this->required_fields = array('newsletter', 'optin');
        $this->table = 'customer';
        $this->className = 'Customer';
        $this->lang = false;
        $this->deleted = true;
        $this->explicitSelect = true;

        $this->allow_export = false;

        $this->addRowAction('contact'); // module cdmoduleca
        $this->addRowAction('ctraite'); // module cdmoduleca
        $this->addRowAction('cinjoignable'); // module cdmoduleca
        $this->addRowAction('crepondeur'); // module cdmoduleca
        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $traite = array(
            'Oui' => 'Oui',
            'Non' => 'Non',
            'Prospect' => 'Prospect'
        );
        $injoignable = array(
            'Oui' => 'Oui',
            'Non' => 'Non'
        );

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $titles_array = array();
        $genders = Gender::getGenders($this->context->language->id);
        foreach ($genders as $gender)
            $titles_array[$gender->id_gender] = $gender->name;

        $this->_select = '
		a.date_add, gl.name as title, traite, injoignable, contacte, 
		(SELECT GROUP_CONCAT(id_group SEPARATOR ", ") FROM `' . _DB_PREFIX_ . 'customer_group` cg  WHERE cg.`id_customer`=a.`id_customer` GROUP by cg.`id_customer`) as id_group,
		(SELECT SUM(total_paid_real / conversion_rate)
			FROM ' . _DB_PREFIX_ . 'orders o
			WHERE o.id_customer = a.id_customer
			' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
			AND o.valid = 1
		) as total_spent, (
			SELECT c.date_add FROM ' . _DB_PREFIX_ . 'guest g
			LEFT JOIN ' . _DB_PREFIX_ . 'connections c ON c.id_guest = g.id_guest
			WHERE g.id_customer = a.id_customer
			ORDER BY c.date_add DESC
			LIMIT 1
		) as connect, (SELECT CONCAT(b.phone, ":", b.phone_mobile) as phone FROM ' . _DB_PREFIX_ . 'address b WHERE b.id_customer = a.id_customer LIMIT 1) as phone
		';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'gender_lang gl ON (a.id_gender = gl.id_gender AND gl.id_lang = ' . (int)$this->context->language->id . ')';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'prospect` AS pr ON a.`id_customer` = pr.`id_customer` ';
        $this->fields_list = array(
            'id_group' => array(
                'title' => $this->l('Gr.'),
                'havingFilter' => true,
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'id_customer' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'title' => array(
                'title' => $this->l('Social title'),
                'filter_key' => 'a!id_gender',
                'type' => 'select',
                'list' => $titles_array,
                'filter_type' => 'int',
                'order_key' => 'gl!name'
            ),
            'lastname' => array(
                'title' => $this->l('Last name')
            ),
            'firstname' => array(
                'title' => $this->l('First name')
            ),
            'email' => array(
                'title' => $this->l('Email address')
            ),
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->l('Company')
                ),
            ));
        }

        $this->fields_list = array_merge($this->fields_list, array(

            'phone' => array(
                'title' => $this->l('Tel'),
                'align' => 'text-center',
                'type' => 'text',
                'orderby' => false,
                'havingFilter' => true,
                'callback' => 'makePhoneCall'),

            'total_spent' => array(
                'title' => $this->l('Sales'),
                'type' => 'price',
                'search' => false,
                'havingFilter' => true,
                'align' => 'text-right',
                'badge_success' => true
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter_key' => 'a!active'
            ),
            'traite' => array(
                'title' => $this->l('Traité'),
                'filter_key' => 'traite',
                'type' => 'select',
                'list' => $traite,
                'order_key' => 'traite',
                'class' => 'fixed-width-xs',
                'callback' => 'doublons',
            ),
            'injoignable' => array(
                'title' => $this->l('Injoignable'),
                'filter_key' => 'injoignable',
                'type' => 'select',
                'list' => $injoignable,
                'order_key' => 'injoignable',
                'class' => 'fixed-width-xs'
            ),
            'contacte' => array(
                'title' => $this->l('Contacté'),
                'filter_key' => 'contacte',
                'type' => 'text',
                'order_key' => 'contacte',
                'callback' => 'contact'
            ),
//			'newsletter' => array(
//				'title' => $this->l('Newsletter'),
//				'align' => 'text-center',
//				'type' => 'bool',
//				'callback' => 'printNewsIcon',
//				'orderby' => false
//			),
//			'optin' => array(
//				'title' => $this->l('Opt-in'),
//				'align' => 'text-center',
//				'type' => 'bool',
//				'callback' => 'printOptinIcon',
//				'orderby' => false
//			),
            'date_add' => array(
                'title' => $this->l('Registration'),
                'type' => 'date',
                'align' => 'text-right'
            ),
//			'connect' => array(
//				'title' => $this->l('Last visit'),
//				'type' => 'datetime',
//				'search' => false,
//				'havingFilter' => true
//			)
        ));

        // Module cdmoduleca
        if (Tools::isSubmit('traite')) {
            ProspectClass::toggleTraite();
        }
        if (Tools::isSubmit('injoignable')) {
            ProspectClass::toggleInjoignable();
        }
        if (Tools::isSubmit('contact')) {
            ProspectClass::setContact();
        }
        if (Tools::isSubmit('repondeur')) {
            ProspectClass::setRepondeur();
        }

        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_CUSTOMER;

        AdminController::__construct();

        $this->addJquery();
        $this->addJS(_PS_MODULE_DIR_ . 'keyyo/views/js/adminkeyyo.js');
        $this->addCSS(_PS_MODULE_DIR_ . 'keyyo/views/css/adminkeyyo.css');
        $this->addCSS(_PS_MODULE_DIR_ . 'cdmoduleca/views/css/admincustomer.css');

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP))
            $this->can_add_customer = false;
    }

    /*
    * module: keyyo
    * date: 2016-08-16 17:17:59
    * version: 1.0.0
    */
    public function initContent()
    {
        if ($this->action == 'select_delete')
            $this->context->smarty->assign(array(
                'delete_form' => true,
                'url_delete' => htmlentities($_SERVER['REQUEST_URI']),
                'boxes' => $this->boxes,
            ));

        if (!$this->can_add_customer && !$this->display)
            $this->informations[] = $this->l('You have to select a shop if you want to create a customer.');

        $keyyo_caller = $this->context->employee->getKeyyoCaller();
        if (!$keyyo_caller) {
            $this->errors[] = 'Veuillez configurer votre numéro d\'appel dans l\'onglet Administration > Employés ';
        }

        AdminController::initContent();
    }

    /*
    * module: keyyo
    * date: 2016-08-16 17:17:59
    * version: 1.0.0
    */
    public function renderView()
    {
        if (!($customer = $this->loadObject()))
            return;

        $this->context->customer = $customer;
        $gender = new Gender($customer->id_gender, $this->context->language->id);
        $gender_image = $gender->getImage();

        $customer_stats = $customer->getStats();
        $sql = 'SELECT SUM(total_paid_real) FROM ' . _DB_PREFIX_ . 'orders WHERE id_customer = %d AND valid = 1';
        if ($total_customer = Db::getInstance()->getValue(sprintf($sql, $customer->id))) {
            $sql = 'SELECT SQL_CALC_FOUND_ROWS COUNT(*) FROM ' . _DB_PREFIX_ . 'orders WHERE valid = 1 AND id_customer != ' . (int)$customer->id . ' GROUP BY id_customer HAVING SUM(total_paid_real) > %d';
            Db::getInstance()->getValue(sprintf($sql, (int)$total_customer));
            $count_better_customers = (int)Db::getInstance()->getValue('SELECT FOUND_ROWS()') + 1;
        } else
            $count_better_customers = '-';

        $orders = Order::getCustomerOrders($customer->id, true);
        $total_orders = count($orders);
        for ($i = 0; $i < $total_orders; $i++) {
            $orders[$i]['total_paid_real_not_formated'] = $orders[$i]['total_paid_real'];
            $orders[$i]['total_paid_real'] = Tools::displayPrice($orders[$i]['total_paid_real'], new Currency((int)$orders[$i]['id_currency']));
        }

        $messages = CustomerThread::getCustomerMessages((int)$customer->id);
        $total_messages = count($messages);
        for ($i = 0; $i < $total_messages; $i++) {
            $messages[$i]['message'] = substr(strip_tags(html_entity_decode($messages[$i]['message'], ENT_NOQUOTES, 'UTF-8')), 0, 75);
            $messages[$i]['date_add'] = Tools::displayDate($messages[$i]['date_add'], null, true);
        }

        $groups = $customer->getGroups();
        $total_groups = count($groups);
        for ($i = 0; $i < $total_groups; $i++) {
            $group = new Group($groups[$i]);
            $groups[$i] = array();
            $groups[$i]['id_group'] = $group->id;
            $groups[$i]['name'] = $group->name[$this->default_form_language];
        }

        $total_ok = 0;
        $orders_ok = array();
        $orders_ko = array();
        foreach ($orders as $order) {
            if (!isset($order['order_state']))
                $order['order_state'] = $this->l('There is no status defined for this order.');

            if ($order['valid']) {
                $orders_ok[] = $order;
                $total_ok += $order['total_paid_real_not_formated'];
            } else
                $orders_ko[] = $order;
        }

        $products = $customer->getBoughtProducts();

        $carts = Cart::getCustomerCarts($customer->id);
        $total_carts = count($carts);
        for ($i = 0; $i < $total_carts; $i++) {
            $cart = new Cart((int)$carts[$i]['id_cart']);
            $this->context->cart = $cart;
            $summary = $cart->getSummaryDetails();
            $currency = new Currency((int)$carts[$i]['id_currency']);
            $carrier = new Carrier((int)$carts[$i]['id_carrier']);
            $carts[$i]['id_cart'] = sprintf('%06d', $carts[$i]['id_cart']);
            $carts[$i]['date_add'] = Tools::displayDate($carts[$i]['date_add'], null, true);
            $carts[$i]['total_price'] = Tools::displayPrice($summary['total_price'], $currency);
            $carts[$i]['name'] = $carrier->name;
        }

        $sql = 'SELECT DISTINCT cp.id_product, c.id_cart, c.id_shop, cp.id_shop AS cp_id_shop
				FROM ' . _DB_PREFIX_ . 'cart_product cp
				JOIN ' . _DB_PREFIX_ . 'cart c ON (c.id_cart = cp.id_cart)
				JOIN ' . _DB_PREFIX_ . 'product p ON (cp.id_product = p.id_product)
				WHERE c.id_customer = ' . (int)$customer->id . '
					AND cp.id_product NOT IN (
							SELECT product_id
							FROM ' . _DB_PREFIX_ . 'orders o
							JOIN ' . _DB_PREFIX_ . 'order_detail od ON (o.id_order = od.id_order)
							WHERE o.valid = 1 AND o.id_customer = ' . (int)$customer->id . '
						)';
        $interested = Db::getInstance()->executeS($sql);
        $total_interested = count($interested);
        for ($i = 0; $i < $total_interested; $i++) {
            $product = new Product($interested[$i]['id_product'], false, $this->default_form_language, $interested[$i]['id_shop']);
            if (!Validate::isLoadedObject($product))
                continue;
            $interested[$i]['url'] = $this->context->link->getProductLink(
                $product->id,
                $product->link_rewrite,
                Category::getLinkRewrite($product->id_category_default, $this->default_form_language),
                null,
                null,
                $interested[$i]['cp_id_shop']
            );
            $interested[$i]['id'] = (int)$product->id;
            $interested[$i]['name'] = Tools::htmlentitiesUTF8($product->name);
        }

        $emails = $customer->getLastEmails();

        $connections = $customer->getLastConnections();
        if (!is_array($connections))
            $connections = array();
        $total_connections = count($connections);
        for ($i = 0; $i < $total_connections; $i++)
            $connections[$i]['http_referer'] = $connections[$i]['http_referer'] ? preg_replace('/^www./', '', parse_url($connections[$i]['http_referer'], PHP_URL_HOST)) : $this->l('Direct link');

        $referrers = Referrer::getReferrers($customer->id);
        $total_referrers = count($referrers);
        for ($i = 0; $i < $total_referrers; $i++)
            $referrers[$i]['date_add'] = Tools::displayDate($referrers[$i]['date_add'], null, true);

        $customerLanguage = new Language($customer->id_lang);
        $shop = new Shop($customer->id_shop);
        $this->tpl_view_vars = array(
            'customer' => $customer,
            'gender' => $gender,
            'gender_image' => $gender_image,
            'registration_date' => Tools::displayDate($customer->date_add, null, true),
            'customer_stats' => $customer_stats,
            'last_visit' => Tools::displayDate($customer_stats['last_visit'], null, true),
            'count_better_customers' => $count_better_customers,
            'shop_is_feature_active' => Shop::isFeatureActive(),
            'name_shop' => $shop->name,
            'customer_birthday' => Tools::displayDate($customer->birthday),
            'last_update' => Tools::displayDate($customer->date_upd, null, true),
            'customer_exists' => Customer::customerExists($customer->email),
            'id_lang' => $customer->id_lang,
            'customerLanguage' => $customerLanguage,
            'customer_note' => Tools::htmlentitiesUTF8($customer->note),
            'messages' => $messages,
            'groups' => $groups,
            'orders' => $orders,
            'orders_ok' => $orders_ok,
            'orders_ko' => $orders_ko,
            'total_ok' => Tools::displayPrice($total_ok, $this->context->currency->id),
            'products' => $products,
            'addresses' => $this->makePhoneCallFiche($customer->getAddresses($this->default_form_language)), 'discounts' => CartRule::getCustomerCartRules($this->default_form_language, $customer->id, false, false),
            'carts' => $carts,
            'interested' => $interested,
            'emails' => $emails,
            'connections' => $connections,
            'referrers' => $referrers,
            'show_toolbar' => true
        );


        return AdminController::renderView();
    }

    /**
     * Création de l'url pour l'appel ajax vers le client depuis un poste KEYYO
     * 02/08/2016 Dominique
     * @param $number
     * @param $params
     * @return string
     */
    /*
	* module: keyyo
	* date: 2016-08-16 17:17:59
	* version: 1.0.0
	*/
    public function makePhoneCall($number, $params)
    {
        $keyyo_link = '';
        $phoneNumbers = explode(':', $number);
        foreach ($phoneNumbers as $phoneNumber) {
            $NumberK = $this->sanitizePhoneNumber($phoneNumber);
            $ln = strlen($NumberK);

            $display_message = ($ln != 10 && $ln > 0) ? '<i class="icon-warning text-danger"></i>' : '';

            $params['lastname'] = str_replace(' ', '_', trim($params['lastname']));
            $params['firstname'] = str_replace(' ', '_', trim($params['firstname']));


            $keyyo_link .= $display_message . ' <a href="' . Context::getContext()->link->getAdminLink('AdminCustomers');
            $keyyo_link .= '&ajax=1&action=KeyyoCall';
            $keyyo_link .= '&CALLEE=' . $NumberK;
            $keyyo_link .= '&CALLE_NAME=' . $params['lastname'] . '_' . $params['firstname'];
            $keyyo_link .= '" class="keyyo_link">' . $NumberK . '</a>';
        }
        return $keyyo_link;
    }

    /*
	* module: keyyo
	* date: 2016-08-16 17:17:59
	* version: 1.0.0
	*/
    private function sanitizePhoneNumber($number)
    {
        $pattern = str_split(Configuration::get('KEYYO_NUMBER_FILTER'));
        $number = str_replace($pattern, '', $number);
        if (substr($number, 0, 1) != '0') {
            $number = '0' . $number;
        }

        return $number;
    }

    /*
	* module: keyyo
	* date: 2016-08-16 17:17:59
	* version: 1.0.0
	*/
    public function ajaxProcessKeyyoCall()
    {

        $keyyo_url = Configuration::get('KEYYO_URL');
        $account = $this->context->employee->getKeyyoCaller();
        $callee = Tools::getValue('CALLEE');
        $calle_name = Tools::getValue('CALLE_NAME');

        if (!$account) {
            $return = Tools::jsonEncode(array('msg' => 'Veuillez configurer votre numéro de compte KEYYO.'));
            die($return);
        }

        if (!$callee || !$calle_name) {
            $return = Tools::jsonEncode(array('msg' => 'Il manque une information pour composer le numéro.'));
            die($return);
        } else {
            $keyyo_link = $keyyo_url . '?ACCOUNT=' . $account;
            $keyyo_link .= '&CALLEE=' . $callee;
            $keyyo_link .= '&CALLE_NAME=' . $calle_name;


            $fp = fopen($keyyo_link, 'r');
            $buffer = fgets($fp, 4096);
            fclose($fp);

            if ($buffer == 'OK') {
                $appels = (int)$_COOKIE['appelKeyyo'] + 1;
                setcookie('appelKeyyo', $appels, strtotime(date('Y-m-d 23:59:59')));
                $return = Tools::jsonEncode(array('msg' => 'Appel du ' . $callee . ' en cours.'));
                die($return);
            } else {
                $return = Tools::jsonEncode(array('msg' => 'Problème lors de l\'appel.'));
                die($return);
            }
        }
    }

    /*
	* module: keyyo
	* date: 2016-08-16 17:17:59
	* version: 1.0.0
	*/
    public function makePhoneCallFiche($customer)
    {
        if (!empty($customer)) {
            $customer[0]['phone'] = ($customer[0]['phone']) ? $this->makeUrlKeyyoFiche($customer[0]['phone'], $customer) : '';
            $customer[0]['phone_mobile'] = ($customer[0]['phone_mobile']) ? $this->makeUrlKeyyoFiche($customer[0]['phone_mobile'], $customer) : '';
        }

        return $customer;
    }

    /*
	* module: keyyo
	* date: 2016-08-16 17:17:59
	* version: 1.0.0
	*/
    public function makeUrlKeyyoFiche($phoneNumber, $customer)
    {
        $keyyo_link = '';
        $NumberK = $this->sanitizePhoneNumber($phoneNumber);
        $ln = strlen($NumberK);

        $display_message = ($ln != 10 && $ln > 0) ? '<i class="icon-warning text-danger"></i>' : '';

        $keyyo_link .= $display_message . ' <a href="' . Context::getContext()->link->getAdminLink('AdminCustomers');
        $keyyo_link .= '&ajax=1&action=KeyyoCall';
        $keyyo_link .= '&CALLEE=' . $NumberK;
        $keyyo_link .= '&CALLE_NAME=' . $customer[0]['lastname'] . '_' . $customer[0]['firstname'];
        $keyyo_link .= '" class="keyyo_link">' . $NumberK . '</a>';

        return $keyyo_link;
    }

    /**
     * Module cdmoduleca
     *
     * @param null $token
     * @param $id
     * @param null $name
     * @return string
     */
    public function displayCtraiteLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');
        if (!array_key_exists('traite', self::$cache_lang))
            self::$cache_lang['traite'] = $this->l('Traité');

        $tpl->assign(array(
            'href' =>
                Context::getContext()->link->getAdminLink('AdminCustomers') . '&'
                . $this->identifier . '=' . $id . '&traite',
            'action' => self::$cache_lang['traite'],
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * Module cdmoduleca
     *
     * @param null $token
     * @param $id
     * @param null $name
     * @return string
     */
    public function displayCinjoignableLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');
        if (!array_key_exists('injoignable', self::$cache_lang))
            self::$cache_lang['injoignable'] = $this->l('Injoignable');

        $tpl->assign(array(
            'href' =>
                Context::getContext()->link->getAdminLink('AdminCustomers') . '&'
                . $this->identifier . '=' . $id . '&injoignable',
            'action' => self::$cache_lang['injoignable'],
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * Module cdmoduleca
     *
     * @param null $token
     * @param $id
     * @param null $name
     * @return string
     */
    public function displayContactLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_default.tpl');
        if (!array_key_exists('contact', self::$cache_lang))
            self::$cache_lang['contact'] = $this->l('Contacté');

        $tpl->assign(array(
            'href' =>
                Context::getContext()->link->getAdminLink('AdminCustomers') . '&'
                . $this->identifier . '=' . $id . '&contact',
            'action' => self::$cache_lang['contact'],
            'id' => $id
        ));

        return $tpl->fetch();
    }

    /**
     * Module cdmoduleca
     *
     * @param null $token
     * @param $id
     * @param null $name
     * @return string
     */
    public function displayCrepondeurLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_default.tpl');
        if (!array_key_exists('repondeur', self::$cache_lang))
            self::$cache_lang['repondeur'] = $this->l('Répondeur');

        $tpl->assign(array(
            'href' =>
                Context::getContext()->link->getAdminLink('AdminCustomers') . '&'
                . $this->identifier . '=' . $id . '&repondeur',
            'action' => self::$cache_lang['repondeur'],
            'id' => $id
        ));

        return $tpl->fetch();
    }

    public function contact($value, $params)
    {
        $v = array(
            'matin' => '',
            'midi' => '',
            'apres_midi' => '',
            'soir' => '',
            'repondeur' => '',
        );
        $messages = array(
            'matin' => '0',
            'midi' => '0',
            'apres_midi' => '0',
            'soir' => '0',
            'repondeur' => '0'
        );


        $contacte = Tools::jsonDecode($value);
        if (isset($contacte->matin)) {
            $messages['matin'] = (count($contacte->matin) > 1) ? '2' : count($contacte->matin);
            foreach ($contacte->matin as $matin) {
                $v['matin'] .= 'Matin : ' . date('à H\hi \l\e d-m', strtotime($matin)) . PHP_EOL;
            }
        }
        if (isset($contacte->midi)) {
            $messages['midi'] = (count($contacte->midi) > 1) ? '2' : count($contacte->midi);
            foreach ($contacte->midi as $midi) {
                $v['midi'] .= 'Midi : ' . date('à H\hi \l\e d-m', strtotime($midi)) . PHP_EOL;
            }
        }
        if (isset($contacte->apres_midi)) {
            $messages['apres_midi'] = (count($contacte->apres_midi) > 1) ? '2' : count($contacte->apres_midi);
            foreach ($contacte->apres_midi as $apmidi) {
                $v['apres_midi'] .= 'Après-midi : ' . date('à H\hi \l\e d-m', strtotime($apmidi)) . PHP_EOL;
            }
        }
        if (isset($contacte->soir)) {
            $messages['soir'] .= (count($contacte->soir) > 1) ? '2' : count($contacte->soir);
            foreach ($contacte->soir as $soir) {
                $v['soir'] .= 'Soir : ' . date('à H\hi \l\e d-m', strtotime($soir)) . PHP_EOL;
            }
        }
        if (isset($contacte->repondeur)) {
            $messages['repondeur'] = (count($contacte->repondeur) > 2) ? '3' : count($contacte->repondeur);
            foreach ($contacte->repondeur as $repondeur) {
                $v['repondeur'] .= 'Répondeur : ' . date('à H\hi \l\e d-m', strtotime($repondeur)) . PHP_EOL;
            }
        }

        $table = '
        <table>
            <tbody>
                <tr class="cd_contacte">
                    <td title="' . $v['matin'] . '" class="cd_cel cd_color_' . $messages['matin'] . '"></td>
                    <td title="' . $v['midi'] . '" class="cd_cel cd_color_' . $messages['midi'] . '"></td>
                    <td title="' . $v['apres_midi'] . '" class="cd_cel cd_color_' . $messages['apres_midi'] . '"></td>
                    <td title="' . $v['soir'] . '" class="cd_cel cd_color_' . $messages['soir'] . '"></td>
                    <td title="' . $v['repondeur'] . '" class="cd_cel cd_color_rep_' . $messages['repondeur'] . '"></td>
                </tr>
            </tbody>
        </table>
        ';

        return $table;
    }

    public function doublons($value, $params)
    {
        $params['lang'] = $this->context->language->id;
        $alert = CaTools::doublons($params);

        if (!empty($alert)) {
            $alert = '<p>' . $alert . '</p>';
        }

        return $value . $alert;
    }

}

