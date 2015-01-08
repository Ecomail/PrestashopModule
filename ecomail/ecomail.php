<?php

if (!defined('_PS_VERSION_'))
  exit;
  
/**
 * 
 * @author Christophe Willemsen
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
	    //var_dump($this->_path);
	    //exit();
	    $output = null;

	    if (Tools::isSubmit('submit'.$this->name))
	    {
	        $ecomail_list_id = Tools::getValue('ecomail_list_id');
			$ecomail_api_key = Tools::getValue('ecomail_api_key');
	        if (!$ecomail_list_id
	          || empty($ecomail_list_id))
	            $output .= $this->displayError($this->l('Invalid Configuration value'));
	        else
	        {
	            Configuration::updateValue('ecomail_list_id', $ecomail_list_id);
				Configuration::updateValue('ecomail_api_key', $ecomail_api_key);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }
	    }
	    return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
	    // Get default language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$options = array();
		
		if(Configuration::get('ecomail_api_key')){
			$lists = json_decode($this->callAPI(Configuration::get('ecomail_api_key'), 'GET', 'lists'));
			foreach($lists->_embedded->lists as $list){
				$options[] = array(
					'id_option' => $list->id,
				    'name' => $list->name
				);
			}
		}
		echo $this->_path;
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
	            ),
	            array(
	                'type' => 'hidden',
	                'name' => 'ecomail_module_path'
	            ),
				array(
				  'type' => 'select',                              // This is a <select> tag.
				  'label' => $this->l('Vyberte list:'),         // The <label> for this <select> tag.
				  'desc' => $this->l('Vyberte list do kterého budou zapsáni noví zákazníci'),  // A help text, displayed right next to the <select> tag.
				  'name' => 'ecomail_list_id',                     // The content of the 'id' attribute of the <select> tag.
				  'required' => true,                              // If set to true, this option must be set.
				  'options' => array(
				    'query' => $options,                           // $options contains the data itself.
				    'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
				    'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
				  )
				),
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
	    $helper->fields_value['ecomail_api_key'] = Configuration::get('ecomail_api_key');
	    $helper->fields_value['ecomail_module_path'] = $this->_path;
		$helper->fields_value['ecomail_list_id'] = Configuration::get('ecomail_list_id');
		
	    return $helper->generateForm($fields_form);
	}
 
	public function hookBackOfficeHeader()
	{
		$js .= '<script type="text/javascript" src="'.$this->_path.'	js/scripts.js"></script>';
		return $js;
	}
		
	public function hookActionCustomerAccountAdd($params)
	{
		//$this->hookAuthentication($params);
		//var_dump($params);
		//$params['_POST']['customer_firstname']
		//$params['_POST']['customer_lastname']
		//$params['_POST']['email']
		//$params['_POST']['newsletter']
		//$params['_POST']['optin']
		if($params['_POST']['newsletter']){
			$this->callAPI(Configuration::get('ecomail_api_key'), 'POST', 'subscribers/'.Configuration::get('ecomail_list_id'), array(
				'email' => $params['_POST']['email'],
				'name' => $params['_POST']['customer_firstname'].' '.$params['_POST']['customer_lastname']
			));
		}
	}
	
	public function callAPI($key, $method, $url, $data = false)
	{
	    $curl = curl_init();

	    switch ($method)
	    {
	        case "POST":
	            curl_setopt($curl, CURLOPT_POST, 1);

	            if ($data)
	                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	            break;
	        case "PUT":
	            curl_setopt($curl, CURLOPT_PUT, 1);
	            break;
	        default:
	            if ($data)
	                $url = sprintf("%s?%s", $url, http_build_query($data));
	    }

	    curl_setopt($curl, CURLOPT_URL, 'http://api.ecomailapp.cz/'.$url.'?key='.$key);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	    $result = curl_exec($curl);

	    curl_close($curl);

	    return $result;
	}
	
}
