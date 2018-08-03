# HookY

A bunch of REDCap hooks to use with REDCap hook framework.

* regular expression validation of field values via action tags (redcap_framework_regexp_validation.php)

## Requirements

* REDCap hook framework

### Install REDCap hook framework

For more details, please refer to the official documentation.

```bash
cd /var/www/redcap
mkdir -P hooks/server/global
cd hooks
git clone git@github.com/123andy/redcap-hook-framework.git framework
```

**NOTE** : Be sure to declare the path of the redcap_hooks.php in the hook section in you Control Center of the REDCap interface to activate hook framework

## Install HookY

```bash
cd /var/www/redcap/hooks/
git clone git@github.com/ylaizet/hooky.git hooky
```

After installing the framework and HookY, the REDCap directory tree should look like this:

* redcap_vx.y.z
* plugins
* hooks
  * framework (framework files)
    * redcap_hooks.php (this is the file that should be referenced in your control center)
  * server (this is a per-instance folder where you add hooks to your server and projects)
    * global (a folder for global hooks)
    * pidxx (a folder for project-specific hooks)
  * hooky (content of this repository)

## Use HookY

Each HookY hook file can be used by a simple include command in a php file named according to the hook function names related to where you want to use the hook.

For instance, to use a hook in a Data Entry forms, you have to create a file named *redcap_data_entry_form.php* and place it either in the *global* or a project specific *pidxx* directory according to your needs.

Here is an example of the content of such a file:

redcap_data_entry_form.php
```php
<?php
	include_once(dirname(__FILE__).'/../../hooky/redcap_framework_regexp_validation.php');
?>
```
## Regular expression validation

This hook triggers an alert box whenever the field value does not match a specified regular expression when the field loose the focus (blur event).

To validate a field, just add the @REGEXP_VALIDATE action tag with your regular expression

```
@REGEXP_VALIDATE=^[0-9]{2}\u0020[a-z]$
```
Currently **no spaces** are permitted with the hooks parser, instead use \u0020.

The example above checks that the field value has exactly 2 digits and one lowercase letter separated by a space.

## Query autocomplete

This hook transforms an input field into an autocomplete populated with a redcap getdata query.

To apply it to a field, just add the @QUERY_AUTOCOMPLETE action tag with a json to set parameters for the getData Query

```
@QUERY_AUTOCOMPLETE={"asLabels":True,"value_format":"%s","value_fields":["redcap_repeat_instance"],"label_format":"[%s]\u0020%s-%s","label_fields":["redcap_repeat_instance","tumorpathologyevent_type","tumorpathologyevent_startdate"],"filter":{"redcap_repeat_instrument":"TumorPathologyEvent"}}
```

**Parameters:**

* `asLabels` : boolean to specify if the data is returned as raw or as label
* `value_fields` : array of redcap field names (variable name) used in `value_format` string formatting
* `value_format` : expression to format the string used as value for the autocomplete list. Each `%s` will be replaced in the expression by it's position related field set in `value_fields` array
* `label_fields` : array of redcap field names (variable name) used in `label_format` string formatting
* `label_format` : expression to format the string used as value for the autocomplete list. Each `%s` will be replaced in the expression by it's position related field set in `label_fields` array
* `filter` : key:values pairs of filtering contrains to apply on data before populating the autocomplete

Currently **no spaces** are permitted with the hooks parser, instead use \u0020.

### Disclaimer

Be aware that no DAG filtering is applied with the @QUERY_AUTOCOMPLETE !
