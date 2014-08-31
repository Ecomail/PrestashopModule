<?php

if (!defined('_PS_VERSION_'))
  exit;
  
/**
 * 
 * @author Ecomail.cz s.r.o.
 */
class Ecomail extends Module {
	
	public function __construct() {
		
		$this->name = 'ecomail';
        $this->tab = 'emailing';
        $this->version = '1.0';
        $this->author = 'Ecomail.cz s.r.o.';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ecomail.cz');
        $this->description = $this->l('Tento modul prováže Váš Prestashop s Vaším účtem u ecomail.cz');

        $this->confirmUninstall = $this->l('Opravdu chcete modul odinstalovat?');

        if (!Configuration::get('ECOMAIL_CZ'))      
          $this->warning = $this->l('No name provided');
		
	}
	
	public function install() {
		return parent :: install()
			&& $this->registerHook('actionCustomerAccountAdd')
			&& $this->registerHook('backOfficeHeader');
	}
	
	public function getContent()
	{
	    $output = null;

	    if (Tools::isSubmit('submit'.$this->name))
	    {
	        $my_module_name = Tools::getValue('ecomail_default_list');
	        if (!$my_module_name
	          || empty($my_module_name))
	            $output .= $this->displayError($this->l('Invalid Configuration value'));
	        else
	        {
	            Configuration::updateValue('ecomail_default_list', $my_module_name);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }
	    }
	    return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
	    // Get default language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

	    // Init Fields form array
	    $fields_form[0]['form'] = array(
	        'legend' => array(
	            'title' => $this->l('Settings'),
	        ),
	        'input' => array(
	            array(
	                'type' => 'text',
	                'label' => $this->l('Vložte Váš API klíč'),
	                'name' => 'ecomail_api_key',
	                'size' => 20,
	                'required' => true
	            )
	        ),
	        'submit' => array(
	            'title' => $this->l('Save'),
	            'class' => 'button'
	        )
	    );

	    $helper = new HelperForm();

	    // Module, token and currentIndex
	    $helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

	    // Language
	    $helper->default_form_language = $default_lang;
	    $helper->allow_employee_form_lang = $default_lang;

	    // Title and toolbar
	    $helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	        'save' =>
	        array(
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ),
	        'back' => array(
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        )
	    );

	    // Load current value
	    $helper->fields_value['ecomail_default_list'] = Configuration::get('ecomail_default_list');

	    return $helper->generateForm($fields_form);
	}
 
	public function hookBackOfficeHeader()
	{
		$js .= '<script type="text/javascript" src="/modules/js/scripts.js"></script>';
		return $js;
	}
		
	public function hookActionCustomerAccountAdd($params)
	{
		//$this->hookAuthentication($params);
		var_dump($params);
		//$params['_POST']['customer_firstname']
		//$params['_POST']['customer_lastname']
		//$params['_POST']['email']
		//$params['_POST']['newsletter']
		//$params['_POST']['optin']
		exit();
	}
	
}
