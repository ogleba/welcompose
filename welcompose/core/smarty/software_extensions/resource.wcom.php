<?php

/**
 * Project: Welcompose
 * File: resource.wcom.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function wcomresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(WCOM_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);

	// get template source
	$tpl_source = $TEMPLATE->smartyFetchTemplate($page_id, $template_type_name);
	
	// when the template source is empty, we cannot distinguish whether
	// the template couldn't be found or the template is empty. So let's
	// throw a more or less meaningful exception.
	if (empty($tpl_source)) {
		throw new Exception('Template not found or empty: '.$tpl_name);
	}
	
	return true;
}

function wcomresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(WCOM_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);
	
	// get template timestamp
	$tpl_timestamp = $TEMPLATE->smartyFetchTemplateTimestamp($page_id, $template_type_name);
	
	if (!empty($tpl_timestamp)) {
		return true;
	} else {
		return false;
	}
}

function wcomresource_isSecure ($tpl_name, &$smarty)
{
    return true;
}

function wcomresource_isTrusted($tpl_name, &$smarty)
{
    // not used for templates
}

?>
