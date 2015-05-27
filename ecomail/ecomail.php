<?php

if (!defined('_PS_VERSION_'))
  exit;
  
/**
 * 
 * @author Christophe Willemsen
 */
class Ecomail extends Module {
	
	public $_html = null;
	
	public function __construct() {
		
		$this->name = 'ecomail';
        if (version_compare(_PS_VERSION_, '1.5', '>'))
		    $this->tab = 'emailing';
		else
		    $this->tab = 'advertising_marketing';
        $this->version = '1.0.2';
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
          
        
        // Checking Extension
		if (!extension_loaded('curl'))
		{
			$this->_html = $this->l('Musíte mít povolenu cURL extension abyste mohli používat tento modul.');
		}
		
	}
	
	public function install() {
		return parent :: install()
			&& $this->registerHook('actionCustomerAccountAdd')
			&& $this->registerHook('backOfficeHeader');
	}
	
	public function getContent()
	{
	    //var_dump($this->_html);
	    //exit();
	    $output = null;
	    if($this->_html){
            return $this->_html;
        }
        
        $output = '<img src="http://www.ecomail.cz/images/logo-black.png"><div class="clear"></div>';
        $output .= '<fieldset style="padding: 25px;border: 2px solid #efefef; margin-bottom: 15px;">
			<div style="float: right; width: 340px; height: 205px; border: dashed 1px #666; padding: 8px; margin-left: 12px; margin-top:-15px;">
			<h2 style="color:#aad03d;">Kontaktujte Ecomail</h2>
			<div style="clear: both;"></div>
			<p> Email : <a href="mailto:support@ecomail.cz" style="color:#aad03d;">support@ecomail.cz</a><br>Phone : +420 777 139 129</p>
			<p style="padding-top:20px;"><b>Pro více informací nás navštivte na:</b><br><a href="http://www.ecomail.cz" target="_blank" style="color:#aad03d;">http://www.ecomail.cz</a></p>
			</div>
			<p>Ecomail plugin Vám pomůže synchronizovat Vaše Prestashop kontakty s vybraným seznamem kontaktů ve Vašem ecomail.cz účtu</p>
			<b>Proč si vybrat Ecomail.cz ?</b>
			<ul class="listt">
				<li> Zaručíme Vám vyšší doručitelnost</li>
				<li> Nejlepší ceny na trhu - lepší nenajdete</li>
				<li> Rychlá a vstřícná podpora</li>
			</ul>
			<b>Co připravujeme k tomuto pluginu do dalších verzí?</b>
			<ul class="listt">
				<li> Užší integraci s akcemi ve Vašem obchodě</li>
				<li> Rozesílání newsletterů přímo z Prestashopu</li>
				<li> Napište nám na <a href="mailto:support@ecomail.cz" style="color:#aad03d;">support@ecomail.cz</a> a my Vám plugin přizpůsobíme!</li>
			</ul><div style="clear:both;">&nbsp;</div>
			</fieldset>';

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
		if(_PS_VERSION_=='1.5.4.0')
            		$js .= '<script type="text/javascript" src="'.$this->_path.'js/scripts15.js"></script>';
        	else
            		$js .= '<script type="text/javascript" src="'.$this->_path.'js/scripts.js"></script>';
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
