# Module Shadab Customcommand

    ``shadab/module-customcommand``


## Main Functionalities
custom admin creation by command

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Shadab`
 - Enable the module by running `php bin/magento module:enable Shadab_Customcommand`
 - Apply database updates by running `php bin/magento setup:upgrade` 
      `php bin/magento setup:di:compile` 
      `php bin/magento setup:static-content:deploy -f` \*
 - Flush the cache by running `php bin/magento cache:flush`
## Specifications

 - Console Command
	- bin/magento customcommand:admincreate --admin-user=shadab --admin-password=abc@123# --admin-firstname=Shadab --admin-lastname=Reza --admin-email=rshadab25@gmail.com


