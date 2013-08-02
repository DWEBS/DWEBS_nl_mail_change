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

	class ModuleDWEBSMailChange extends \Module{
		
		protected $strTemplate = 'DWEBS_nl_mail_change_default';
		
		
		/**
		 * Display a wildcard in the back end
		 * @return string
		 */
		public function generate(){
			if (TL_MODE == 'BE'){
				$objTemplate = new \BackendTemplate('be_wildcard');
	
				$objTemplate->wildcard = '### DWEBS NEWSLETTER MAIL CHANGE ###';
				$objTemplate->title = $this->headline;
				$objTemplate->id = $this->id;
				$objTemplate->link = $this->name;
				$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
	
				return $objTemplate->parse();
			}
			
			$this->nl_channels = deserialize($this->nl_channels);
	
			// Return if there are no channels
			if (!is_array($this->nl_channels) || empty($this->nl_channels)){
				return '';
			}
	
			return parent::generate();
		}
		
		protected function compile(){
			
			// Overwrite default template
			if ($this->nl_template){
				$this->Template = new \FrontendTemplate($this->nl_template);
				$this->Template->setData($this->arrData);
			}
			
			// Change e-mail address
			if (\Input::post('FORM_SUBMIT') == 'tl_change_mail'){
				$this->changeEmail();
				return;
			}
			
			$arrChannels = array();
			$objChannel = \NewsletterChannelModel::findByIds($this->nl_channels);
	
			// Get the titles
			if ($objChannel !== null){
				while ($objChannel->next())
				{
					$arrChannels[$objChannel->id] = $objChannel->title;
				}
			}
			
			$blnHasError = false;

			// Error message
			if (strlen($_SESSION['EMAIL_CHANGE_ERROR'])){
				$blnHasError  = true;
				$this->Template->mclass = 'error';
				$this->Template->message = $_SESSION['EMAIL_CHANGE_ERROR'];
				$_SESSION['EMAIL_CHANGE_ERROR'] = '';
			}
			
			// Confirmation message
			if (strlen($_SESSION['EMAIL_CHANGE_CONFIRM'])){
				$this->Template->mclass = 'confirm';
				$this->Template->message = $_SESSION['EMAIL_CHANGE_CONFIRM'];
				$_SESSION['EMAIL_CHANGE_CONFIRM'] = '';
			}
			
			$this->Template->channels = $arrChannels;
			$this->Template->showChannels = !$this->nl_hideChannels;
			$this->Template->submit = specialchars($GLOBALS['TL_LANG']['MSC']['DWEBS_nl_mail_change_send']);
			$this->Template->channelsLabel = $GLOBALS['TL_LANG']['MSC']['nl_channels'];
			$this->Template->oldMailLabel = $GLOBALS['TL_LANG']['MSC']['DWEBS_nl_mail_change_oldMail'];
			$this->Template->newMailLabel = $GLOBALS['TL_LANG']['MSC']['DWEBS_nl_mail_change_newMail'];
			$this->Template->action = $this->getIndexFreeRequest();
			$this->Template->formId = 'tl_change_mail';
			$this->Template->id = $this->id;
			$this->Template->hasError = $blnHasError;
		}	

		protected function changeEmail(){
			
			$arrChannels = \Input::post('channels');
			if (!is_array($arrChannels)){
				$_SESSION['EMAIL_CHANGE_ERROR'] = $GLOBALS['TL_LANG']['ERR']['DWEBS_nl_mail_change_noChannels'];
				$this->reload();
			}
			
			$arrChannels = array_intersect($arrChannels, $this->nl_channels);

			// Check the selection
			if (!is_array($arrChannels) || empty($arrChannels)){
				$_SESSION['EMAIL_CHANGE_ERROR'] = $GLOBALS['TL_LANG']['ERR']['DWEBS_nl_mail_change_noChannels'];
				$this->reload();
			}
	
			$newMail = \Idna::encodeEmail(\Input::post('newMail', true));
			// Validate new e-mail address
			if (!\Validator::isEmail($newMail)){
				$_SESSION['EMAIL_CHANGE_ERROR'] = $GLOBALS['TL_LANG']['ERR']['DWEBS_nl_mail_change_email'];
				$this->reload();
			}
	
			$oldMail = \Idna::encodeEmail(\Input::post('oldMail', true));
			// Validate old e-mail address
			if (!\Validator::isEmail($oldMail)){
				$_SESSION['EMAIL_CHANGE_ERROR'] = $GLOBALS['TL_LANG']['ERR']['DWEBS_nl_mail_change_email'];
				$this->reload();
			}
			
			// Remove old subscriptions that have not been activated yet
			if (($objOld = \NewsletterRecipientsModel::findBy(array("email=? AND active=''"), $oldMail)) !== null){
				while ($objOld->next()){
					$objOld->delete();
				}
			}

			$time = time();
			
			foreach ($arrChannels as $id){
				$sql = "UPDATE tl_newsletter_recipients SET email=?, tstamp=? WHERE email=? AND pid=?";
				$this->Database
					->prepare($sql)
					->execute($newMail,$time,$oldMail,$id);
			}
			
			// Redirect to the jumpTo page
			if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) !== null){
				$this->redirect($this->generateFrontendUrl($objTarget->row()));
			}
			
			$_SESSION['EMAIL_CHANGE_CONFIRM'] = specialchars($GLOBALS['TL_LANG']['MSC']['DWEBS_nl_mail_change_confirm']);
			$this->reload();
			
		}
		
	}

?>