# REDCap HookY

A bunch of REDCap hooks for REDCap hook framework.

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
Currently** no spaces** are permitted with the hooks parser, instead use \u0020.

The example above checks that the field value has exactly 2 digits and one lowercase letter separated by a space.