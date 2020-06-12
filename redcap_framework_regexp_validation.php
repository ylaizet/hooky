<?php

/**
	Hook utility to validate a field value against a regular expression

	(Currently no spaces are permitted with the hooks parser)
	To use a space inside the regular expression pattern, use \u0020

		@REGEXP_VALIDATE=^[0-9]{2}$

	On blur event, an alert box is raised when the field value is not valid.

	Author: Y. Laizet
	Version: 0.1.0
	Licence: GPL-2.0

	(Based on Andrew Martin redcap framework example)
**/


$term = '@REGEXP_VALIDATE';
$error_message_tag = '@REGEXP_ERR_MSG';
//hook_log("Starting $term for project $project_id", "DEBUG");

///////////////////////////////
//	Enable hook_functions and hook_fields for this plugin (if not already done)
if (!isset($hook_functions)) {
	$file = HOOK_PATH_ROOT . 'framework/resources/init_hook_functions.php';
	if (file_exists($file)) {
		include_once $file;

		// Verify it has been loaded
		if (!isset($hook_functions)) { hook_log("ERROR: Unable to load required init_hook_functions."); return; }
	} else {
		hook_log ("ERROR: In Hooks - unable to include required file $file while in " . __FILE__);
	}
}

// See if the term defined in this hook is used on this page
if (!isset($hook_functions[$term])) {
	hook_log ("Skipping $term on $instrument of $project_id - not used.", "DEBUG");
	return;
}
//////////////////////////////

$startup_vars = array();
foreach($hook_functions[$term] as $field => $details) {
	$i = new stdClass();
	$i->fieldName = $field;
	$i->params = $details['params'];
	$i->err_msg = isset($hook_functions[$error_message_tag][$field]) ? $hook_functions[$error_message_tag][$field]['params'] : "";
	$startup_vars[] = $i;
}
?>


<script type='text/javascript'>
$(document).ready(function() {
	var lookupFields = <?php print json_encode($startup_vars); ?>;
	//console.log("LookupFields:");console.log(lookupFields);

	// Put a link over destination fields to show they are linked to a master lookup field
	function check_pattern(event) {
		if (!event.target.validity.valid) {
			var pattern_error_msg = 'The value in ' + event.data.field_name + ' does not match the requested pattern: ' + event.data.params;
			if (event.data.err_msg) {
				pattern_error_msg = event.data.err_msg.split('\\u0020').join(' ') + "\n\n" + event.data.params;
			}
			//event.target.setCustomValidity(pattern_error_msg);
			$(event.target).attr("style", "font-weight: bold; background-color: rgb(255, 183, 190);");
			alert(pattern_error_msg);
//			setTimeout(function() {$(event.target).focus()}, 0);//Inactivated focus otherwise it makes a loop of alerts in case of multiple errors
		} else {
			$(event.target).attr("style", "font-weight: normal; background-color: rgb(255, 255, 255);");
		}
	}

	// Loop through each lookup field and add an event handler
	$(lookupFields).each(function(i, obj) {
		var field_name = obj.fieldName;
		var params = obj.params;
		var err_msg = obj.err_msg;
		//console.log('i: ' + i);console.log(field_name);console.log(params);

		// Add event handler to blur event
		if (params) {
			var input = $('input[name="' + field_name + '"]');
			$(input).attr("pattern", params);
			$(input).on("blur", {params: params, err_msg: err_msg, field_name: field_name}, check_pattern);
		}
	});
});
</script>

