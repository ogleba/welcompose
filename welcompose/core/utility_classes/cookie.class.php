<?php

/**
 * Project: Welcompose
 * File: cookie.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

class Utility_Cookie {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Reference to admin cookie
	 * @var object
	 */
	protected $admin_cookie = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
		// load cookie class
		if (!class_exists('Base_Cookie')) {
			$cookie_class_path = dirname(__FILE__).'/../base_classes/cookie.class.php';
			require(Base_Compat::fixDirectorySeparator($cookie_class_path));
		}
		
		// create instance of cookie class for admin cookie
		$this->admin_cookie = new Base_Cookie($this->base->_conf['environment']['app_key'], 'wcom_admin');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Singleton. Returns instance of the Utility_Cookie object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_Cookie::$instance == null) {
		Utility_Cookie::$instance = new Utility_Cookie(); 
	}
	return Utility_Cookie::$instance;
}

/**
 * Sets cookie with new default project. Takes new project id as first
 * argument. Returns bool.
 *
 * @throws Utility_CookieException
 * @param int Project id
 * @return bool
 */
public function adminSwitchCurrentProject ($new_project = null)
{
	// input check
	if (!empty($new_project) && !is_numeric($new_project)) {
		throw new Utility_CookieProject("Input for parameter new_project is expected to be numeric");
	}
	
	// check cookie data
	$this->adminCheckCookieData();
	
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// set new default project
	$data['current_project'] = $new_project;
	
	// write data back to cookie
	$this->admin_cookie->setCookieData($data);
	
	// set new cookie
	return $this->admin_cookie->setCookie();	
}

/**
 * Returns current project id from admin cookie. If no current project
 * can be found, null will be returned.
 * 
 * @return int
 */
public function adminGetCurrentProject ()
{
	// check cookie data
	$this->adminCheckCookieData();
	
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// get current project
	$current_project = Base_Cnc::filterRequest($data['current_project'], WCOM_REGEX_NUMERIC);
	
	// return current project
	return $current_project;
}

/**
 * Makes sure that the cookie data structure is an array.
 */
protected function adminCheckCookieData ()
{
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// make sure that the data structure is an array
	if (!is_array($data)) {
		$data = array();
	}
	
	// write data back to cookie class
	$this->admin_cookie->setCookieData($data);
}

// end of class
}

class Utility_CookieException extends Exception { }

?>