<?php

/**
 * Project: Oak
 * File: resource.oakgtpl.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

function oakgtplresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// sanitize tpl_name
	$tpl_name = trim(strip_tags($tpl_name));
	
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// fetch global template from database
	$template = (array)$GLOBALTEMPLATE->smartyFetchGlobalTemplate($tpl_name);

	// if there's no template id, we can be sure that the global template wasn't found.
	if (!array_key_exists('id', $template)) {
		return false;
	}

	// set mime type constant
	define("OAK_GLOBAL_TEMPLATE_MIME_TYPE",
		(!empty($content['mime_type']) ? $content['mime_type'] : 'text/plain'));
	
	// assign template source
	$tpl_source = $template['content'];
	
	return true;
}

function oakgtplresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// sanitize tpl_name
	$tpl_name = trim(strip_tags($tpl_name));
	
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// fetch last modification date from database
	$tpl_timestamp = $GLOBALTEMPLATE->smartyFetchGlobalTemplateTimestamp($tpl_name);
	
	if (!empty($tpl_timestamp)) {
		return true;
	} else {
		return false;
	}
}

function oakgtplresource_isSecure ($tpl_name, &$smarty)
{
    return true;
}

function oakgtplresource_isTrusted($tpl_name, &$smarty)
{
    // not used for templates
}

?>