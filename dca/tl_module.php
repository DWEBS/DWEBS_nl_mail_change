<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright  DWEBS - Robin Steinheimer - 2013 <http://DWEBS.org>
 * @author     Robin Steinheimer
 * @package    DWEBS_nl_mail_change
 * @license    LGPL
 * @filesource
 * @see        https://github.com/DWEBS/DWEBS_nl_mail_change
 */



$GLOBALS['TL_DCA']['tl_module']['palettes']['DWEBS_nl_mail_change']    = '{title_legend},name,headline,type;{config_legend},nl_channels,nl_hideChannels;{redirect_legend},jumpTo;{template_legend:hide},nl_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['nl_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['nl_template'],
	'default'                 => 'mod_ec_default',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_newsletter_mail_change', 'getTemplates'),
	'sql'                     => "varchar(32) NOT NULL default ''"
);


/**
 * Class tl_module_newsletter_mail_change
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  DWEBS - Robin Steinheimer - 2013 <http://DWEBS.org>
 * @author     Robin Steinheimer
 * @package    DWEBS_nl_mail_change
 */
class tl_module_newsletter_mail_change extends Backend{

	/**
	 * Import the back end user object
	 */
	public function __construct(){
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Get all channels and return them as array
	 * @return array
	 */
	public function getChannels(){
		if (!$this->User->isAdmin && !is_array($this->User->newsletters)){
			return array();
		}

		$arrChannels = array();
		$objChannels = $this->Database->execute("SELECT id, title FROM tl_newsletter_channel ORDER BY title");

		while ($objChannels->next()){
			if ($this->User->isAdmin || $this->User->hasAccess($objChannels->id, 'newsletters')){
				$arrChannels[$objChannels->id] = $objChannels->title;
			}
		}

		return $arrChannels;
	}
	
	public function getTemplates(){
		return $this->getTemplateGroup('DWEBS_nl_');
	}
	
}
