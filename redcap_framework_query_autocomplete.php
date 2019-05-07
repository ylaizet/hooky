<?php

/**
	Hook utility to transform an input redcap field into an autocomplete populated with a redcap REDCap::getData query

	(Currently no spaces are permitted with the hooks parser)
	Use \u0020 where you need a space char

	Examples of Action tags:
		@QUERY_AUTOCOMPLETE={"value_format":"%s","value_fields":["repet_id"],"label_format":"[%s]\040%s-%s","label_fields":["repet_id","repet_input","repet_list"],"filter":{"redcap_repeat_instrument":"repetition"}}
		@QUERY_AUTOCOMPLETE={"asLabels":True,"value_format":"%s","value_fields":["redcap_repeat_instance"],"label_format":"[%s]\u0020%s-%s","label_fields":["redcap_repeat_instance","tumorpathologyevent_type","tumorpathologyevent_startdate"],"filter":{"redcap_repeat_instrument":"TumorPathologyEvent"}}

	As redcap 7.3.0 do not seem to support redcap_repeat_instrument or redcap_repeat_instance in filterLogic
	or in fields, set a filter key with one of them so that data from all fields in fetched, then filtered
	according to the field => value set in the filter key.

	Author: Y. Laizet <y.laizet@bordeaux.unicancer.fr>
	Version: 0.1.0
	Licence: GPL-2.0

	(Based on Andrew Martin redcap framework example)
**/


$term = '@QUERY_AUTOCOMPLETE';
hook_log("Starting $term for project $project_id", "DEBUG");

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
	$params = $details['params'];
	try {
		$params = json_decode($params);
	} catch (Exception $e) {
		hook_log('Caught exception decoding params in $term for project $project_id: ' . $e->getMessage(), "ERROR");
		$params = $details['params'];
	}
	$i = new stdClass();
	$i->fieldName = $field;
	$i->params = $params;
	if (property_exists($params, "filter")) {
		if (property_exists($params->filter, "redcap_repeat_instrument") || property_exists($params->filter, "redcap_event_name") || property_exists($params->filter, "redcap_repeat_instance")) {
			// Get all fields then filter because redcap repeat fields are not (yet in 7.3) supported in the array of field of getData method
			$data = json_decode(REDCap::getData($project_id, 'json', array($record), NULL, NULL, NULL, FALSE, FALSE, FALSE, NULL, $params->asLabels), true);
		} else {
			$data = json_decode(REDCap::getData($project_id, 'json', array($record), array_merge((array) $params->value_fields, (array) $params->label_fields), NULL, NULL, FALSE, FALSE, FALSE, NULL, $params->asLabels), true);
		}
		$data_filtered = array();
		foreach ($data as $item) {
			$keep_item = TRUE;
			foreach ($params->filter as $filter_key => $filter_value) {
				if ($item[$filter_key] != $filter_value) {
					$keep_item = FALSE;
				}
			}
			if ($keep_item == TRUE) {
				$data_filtered[] = $item;
			}
		}
		$data = $data_filtered;
	} else {
		$data = json_decode(REDCap::getData($project_id, 'json', array($record), array_merge((array) $params->value_fields, (array) $params->label_fields), NULL, NULL, FALSE, FALSE, FALSE, NULL, $params->asLabels), true);
	}
	$data_items = array();
	foreach ($data as $item) {
		$value_field_values = array();
		foreach ($params->value_fields as $value_field) {
			$value_field_values[] = $item[$value_field];
		}
		$label_field_values = array();
		if ($params->key_in_value)
		{
			$label_field_values = array_merge(array(), $value_field_values);
		}
		foreach ($params->label_fields as $label_field) {
			$label_field_values[] = $item[$label_field];
		}
		$data_items[] = array('value' => vsprintf($params->value_format, $value_field_values), "label" => vsprintf($params->label_format, $label_field_values));
	}
	$i->data = $data_items;
	$i->all = $data;
	$startup_vars[] = $i;
}
?>


<script type='text/javascript'>
$(document).ready(function() {
	var lookupFields = <?php print json_encode($startup_vars); ?>;

	// Loop through each lookup field and add an event handler
	$(lookupFields).each(function(i, obj) {

		// Add event handler to blur event
		if (obj.params) {
			$('<br><span id="query_autocomplete_' + obj.fieldName + '"></span>').insertAfter($('input[name="' + obj.fieldName + '"]'));
			$('input[name="' + obj.fieldName + '"]').autocomplete({
				source: obj.data,
				minLength: 0,
				select: function (event, ui) {
					$('#query_autocomplete_' + obj.fieldName).text(ui.item.label);
					$(this).val(ui.item ? ui.item : "");
				},
				change: function (event, ui) {
					if (!ui.item) {
					$('#query_autocomplete_' + obj.fieldName).text("");
						this.value = '';}
				}
			})
			.blur(function(event){
				if (!event.target.value) {
					$('#query_autocomplete_' + obj.fieldName).text("");
				}
			})
			.focus(function(){
				//Show list on focus
				$(this).data("uiAutocomplete").search($(this).val());
			});
		}
	});
});
</script>

