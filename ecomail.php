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
			&& $this->registerHook('actionCustomerAccountAdd');
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
