<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @package DWEBS_nl_mail_change
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'ModuleDWEBSMailChange' => 'system/modules/DWEBS_nl_mail_change/modules/ModuleDWEBSMailChange.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'DWEBS_nl_mail_change_default' => 'system/modules/DWEBS_nl_mail_change/templates',
));
