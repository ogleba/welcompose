<?php

/**
 * Project: Oak
 * File: antispamplugins_add.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// define area constant
define('OAK_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load antispamplugin class
	/* @var $ANTISPAMPLUGIN Community_AntiSpamPlugin */
	$ANTISPAMPLUGIN = load('Community:AntiSpamPlugin');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get anti spam plugin
	$plugin = $ANTISPAMPLUGIN->selectAntiSpamPlugin(Base_Cnc::filterRequest($_REQUEST['id'],
		OAK_REGEX_NUMERIC));
	
	// prepare types
	$types = array(
		'comment' => gettext('Comment plugin'),
		'trackback' => gettext('Trackback plugin')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('anti_spam_plugin', 'post');
	$FORM->registerRule('testForNameUniqueness', 'callback', 'testForUniqueName', $ANTISPAMPLUGIN);
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// select for type
	$FORM->addElement('select', 'type', gettext('Plugin type'), $types,
		array('id' => 'anti_spam_plugin_type'));
	$FORM->applyFilter('type', 'trim');
	$FORM->applyFilter('type', 'strip_tags');
	$FORM->addRule('type', gettext('Chosen plugin type is out of range'),
		'in_array_keys', $types);
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'anti_spam_plugin_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	$FORM->addRule('name', gettext('An anti spam plugin with the desired name is already registered'),
		'testForUniqueName', $FORM->exportValue('id'));
	
	// textfield for internal name
	$FORM->addElement('text', 'internal_name', gettext('Internal name'), 
		array('id' => 'anti_spam_plugin_internal_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('internal_name', 'trim');
	$FORM->applyFilter('internal_name', 'strip_tags');
	$FORM->addRule('internal_name', gettext('Please enter an internal name'), 'required');
	$FORM->addRule('internal_name', gettext('Please enter a valid internal name'), 'regex',
		OAK_REGEX_ANTI_SPAM_PLUGIN_INTERNAL_NAME);

	// textfield for priority
	$FORM->addElement('text', 'priority', gettext('Priority'), 
		array('id' => 'anti_spam_plugin_priority', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('priority', 'trim');
	$FORM->applyFilter('priority', 'strip_tags');
	$FORM->addRule('priority', gettext('Please enter a priority'), 'required');
	$FORM->addRule('priority', gettext('Please enter a numeric priority'), 'numeric');
	
	// checkbox for active
	$FORM->addElement('checkbox', 'active', gettext('Active'), null,
		array('id' => 'anti_spam_plugin_active', 'class' => 'chbx'));
	$FORM->applyFilter('active', 'trim');
	$FORM->applyFilter('active', 'strip_tags');
	$FORM->addRule('active', gettext('The field whether the plugin is active accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update anti spam plugin'),
		array('class' => 'submitbut200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($plugin['id'], null),
		'type' => Base_Cnc::ifsetor($plugin['type'], null),
		'name' => Base_Cnc::ifsetor($plugin['name'], null),
		'internal_name' => Base_Cnc::ifsetor($plugin['internal_name'], null),
		'priority' => Base_Cnc::ifsetor($plugin['priority'], null),
		'active' => Base_Cnc::ifsetor($plugin['active'], null)
	));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));

		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('oak_admin_root_www',
			$BASE->_conf['path']['oak_admin_root_www']);
		
	    // build session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign prepared session array to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('community/antispamplugins_edit.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['project'] = OAK_CURRENT_PROJECT;
		$sqlData['type'] = $FORM->exportValue('type');
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['internal_name'] = $FORM->exportValue('internal_name');
		$sqlData['priority'] = $FORM->exportValue('priority');
		$sqlData['active'] = $FORM->exportValue('active');
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$ANTISPAMPLUGIN->updateAntiSpamPlugin($FORM->exportValue('id'),
				$sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: antispamplugins_select.php");
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>