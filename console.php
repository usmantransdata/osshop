<?php

$microtime = microtime(true);
function t()
{
    global $microtime;
    return round(microtime(true) - $microtime, 5);
}

require_once 'bootstrap.php';
oxRegistry::getLang()->getBaseLanguage();
set_time_limit(0);

switch (mb_strtolower($argv[1])) {
    case 'db:views':
    case 'db:view':
        echo "Generating database views...\n";
        /** @var oxDbMetaDataHandler $oMetaData */
        $oMetaData = oxNew('oxDbMetaDataHandler');
        $oMetaData->updateViews();

        echo "Done. (" . t() . ")\n\n";
        break;

    case 'db:delete':
        $aTables = oxDb::getDb()->getAll('show tables');
        foreach ($aTables as $aTable) {
            if(strpos($aTable[0], "oxv_") !== false) {
                oxDb::getDb()->execute("DROP VIEW `" . $aTable[0] . "`");
            }
            else {
                oxDb::getDb()->execute("DROP TABLE `" . $aTable[0] . "`");
            }
        }
        echo "Done. (" . t() . ")\n\n";
        break;

    case 'cache:lang':
    case 'cache:language':
    case 'cache:trans':
        echo "Resetting language cache ...\n";
        oxRegistry::getUtils()->resetLanguageCache();
        echo "Done. (" . t() . ")\n\n";
        break;

    case 'cache:oxid':
        echo "Resetting cache ...\n";
        oxRegistry::getUtils()->oxResetFileCache();
        echo "Done. (" . t() . ")\n\n";
        break;

    case 'cache:all':
        echo "Resetting all cache ...\n";

        $aFiles = glob('tmp/*');
        foreach ($aFiles as $sFilePath) {
            if (basename($sFilePath) == '.htaccess' || is_dir($sFilePath)) {
                continue;
            }
            unlink($sFilePath);
        }
        $aFiles = glob('tmp/smarty/*');
        foreach ($aFiles as $sFilePath) {
            if (basename($sFilePath) == '.htaccess' || is_dir($sFilePath)) {
                continue;
            }
            unlink($sFilePath);
        }
        echo "Done. (" . t() . ")\n\n";
        break;
    case 'cache:log':
        file_put_contents("log/EXCEPTION_LOG.txt", "");
        break;
    case 'mod:on':
    case 'mod:enable':
    case 'module:on':
    case 'module:enable':
        echo "Enabling module ...\n";

        $sModuleId = @$argv[2];
        while(empty($sModuleId)) {
            echo "Enter module ID: ";
            $sModuleId = trim(fgets(STDIN));
        }

        /** @var oxModule $module */
        $module = oxNew('oxModule');
        if (!$module->load($argv[2])) {
            echo "Load error!\n\n";
            return;
        }

        if ($module->activate()) {

	        $aModulePaths = $module->getModulePaths();

	        if ( !is_array($aModulePaths) || !array_key_exists( $argv[2], $aModulePaths ) ) {
		        $oConfig = $module->getConfig();
		        $aModulePaths[$argv[2]] = $argv[2];
		        $oConfig = $module->getConfig();
		        $oConfig->saveShopConfVar( 'aarr', 'aModulePaths', $aModulePaths );
	        }

	        echo "Done. (" . t() . ")\n\n";
        } else {
            echo "Error!\n\n";
        }
        break;

    case 'mod:off':
    case 'mod:disable':
    case 'module:off':
    case 'module:disable':
        echo "Disabling module ...\n";

        $sModuleId = @$argv[2];
        while(empty($sModuleId)) {
            echo "Enter module ID: ";
            $sModuleId = trim(fgets(STDIN));
        }

        /** @var oxModule $module */
        $module = oxNew('oxModule');
        if (!$module->load($argv[2])) {
            echo "Load error!\n\n";
            return;
        }

        if ($module->deactivate()) {
            echo "Done. (" . t() . ")\n\n";
        } else {
            echo "Error!\n\n";
        }

        break;
    case 'mod:i':
    case 'mod:info':
    case 'module:i':
    case 'module:info':
        echo "Module info ...\n";

        $sModulesDir = oxRegistry::getConfig()->getModulesDir();
        /** @var oxModuleList $oModuleList */
        $oModuleList = oxNew("oxModuleList");
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);

        echo str_repeat("-", 120);
        echo "\n";

        echo str_pad("| " . "TITLE", 40, " ", STR_PAD_RIGHT);
        echo str_pad("| " . "ID", 40, " ", STR_PAD_RIGHT);
        echo str_pad("| " . "STATE", 40, " ", STR_PAD_RIGHT);
        echo "|";
        echo "\n";

        echo str_repeat("-", 120);
        echo "\n";

        foreach ($aModules as $aModule) {
            echo str_pad("| " . $aModule->getTitle(), 40, " ", STR_PAD_RIGHT);
            echo str_pad("| " . $aModule->getModulePath(), 40, " ", STR_PAD_RIGHT);
            echo str_pad("| " . ($aModule->isActive() ? "Active" : "Not active"), 40, " ", STR_PAD_RIGHT);
            echo "|";
            echo "\n";
        }

        echo str_repeat("-", 120);
        echo "\n";

        break;
    case 'config:get':
    case 'conf:get':
    case 'settings:get':
    case 'setting:get':
        echo "Loading config ...\n";

        $sUsername = @$argv[2];
        while(empty($sUsername)) {
            echo "Enter config name: ";
            $sUsername = trim(fgets(STDIN));
        }

        echo "\n";
        var_dump(oxRegistry::getConfig()->getConfigParam($sUsername));
        echo "\nDone. (" . t() . ")\n\n";
        break;

    case 'config:set':
    case 'conf:set':
    case 'settings:set':
    case 'setting:set':
        echo "Saving config ...\n";

        $sUsername = @$argv[2];
        while(empty($sUsername)) {
            echo "Enter config name: ";
            $sUsername = trim(fgets(STDIN));
        }

        $sConfigValue = @$argv[3];
        while(empty($sConfigValue) && $sConfigValue != 0) {
            echo "Enter config value: ";
            $sConfigValue = trim(fgets(STDIN));
        }

        $sType = $argv[4];
        if (!$sType) {
            $sType = 'str';
        }

        oxRegistry::getConfig()->saveShopConfVar($sType, $sUsername, $sConfigValue, null, (isset($argv[5]) ? $argv[5] : ''));
        echo "Done. (" . t() . ")\n\n";
        break;

    case 'user:change:password':
        echo "Changing password ...\n";

        $sUsername = @$argv[2];
        while(empty($sUsername)) {
            echo "Enter username: ";
            $sUsername = trim(fgets(STDIN));
        }

        $sPassword = @$argv[3];
        while(empty($sPassword)) {
            echo "Enter new password: ";
            $sPassword = trim(fgets(STDIN));
        }

        /** @var oxUser $oUser */
        $oUser = oxNew('oxUser');

        $sUserId = $oUser->getIdByUserName($sUsername);
        if(empty($sUserId)) {
            echo "Not found user by username: " . $sUsername . "!\n\n";
            return;
        }

        $oUser->load($sUserId);
        $oUser->setPassword($sPassword);
        $oUser->save();

        echo "Done. (" . t() . ")\n\n";
        break;

    case 'user:change:rights':
        echo "Changing rights ...\n";

        $sUsername = @$argv[2];
        while(empty($sUsername)) {
            echo "Enter username: ";
            $sUsername = trim(fgets(STDIN));
        }

        $sRights = @$argv[3];
        while(empty($sRights) || !in_array($sRights, array('user', 'malladmin'))) {
            echo "Enter new rights (user|malladmin): ";
            $sRights = trim(fgets(STDIN));
        }

        /** @var oxUser $oUser */
        $oUser = oxNew('oxUser');

        $sUserId = $oUser->getIdByUserName($sUsername);
        if(empty($sUserId)) {
            echo "Not found user by username: " . $sUsername . "!\n\n";
            return;
        }

        oxDb::getInstance()->getDb()->execute("UPDATE oxuser SET oxrights = '{$sRights}' WHERE oxid = '{$sUserId}' LIMIT 1");

        echo "Done. (" . t() . ")\n\n";
        break;

    case 'user:change:country':
        echo "Changing country...\n";

        $sUsername = @$argv[2];
        while(empty($sUsername)) {
            echo "Enter username: ";
            $sUsername = trim(fgets(STDIN));
        }

        $aCountries = array( 'lt' => 'c97a9e28e5a92e66002b4b80e9a2ef9d');
        $sCountry = @$argv[3];
        while(empty($sCountry) || !in_array($sCountry, array('lt'))) {
            echo "Enter new country (lt): ";
            $sCountry = trim(fgets(STDIN));
        }

        /** @var oxUser $oUser */
        $oUser = oxNew('oxUser');

        $sUserId = $oUser->getIdByUserName($sUsername);
        if(empty($sUserId)) {
            echo "Not found user by username: " . $sUsername . "!\n\n";
            return;
        }

        oxDb::getInstance()->getDb()->execute("UPDATE oxuser SET oxcountryid = '{$aCountries[$sCountry]}' WHERE oxid = '{$sUserId}' LIMIT 1");

        break;

    case 'currency':
    case 'curr':
        echo "Changing currency...\n";

        $sCurrName = @$argv[2];
        while(empty($sCurrName)) {
            echo "Enter currency name (ngn|eur|usd): ";
            $sCurrName = trim(fgets(STDIN));
        }

        $aCurrencies = array(
            'ngn' => array(
                'name'      => 'NGN',
                'rate'      => '1',
                'dec'       => ',',
                'thousand'  => '',
                'sign'      => '₦',
                'decimal'   => 2
            ),
            'eur' => array(
                'name'      => 'EUR',
                'rate'      => '1',
                'dec'       => ',',
                'thousand'  => '',
                'sign'      => '€',
                'decimal'   => 2
            ),
            'usd' => array(
                'name'      => 'USD',
                'rate'      => '1',
                'dec'       => ',',
                'thousand'  => '',
                'sign'      => '$',
                'decimal'   => 2
            )
        );

        $aCurrenciesArray[] = implode('@', $aCurrencies[$sCurrName]);

        oxRegistry::getConfig()->saveShopConfVar('arr', 'aCurrencies', $aCurrenciesArray);

        echo "Done. (" . t() . ")\n\n";
        break;

    case 'soundest:restore':
        echo "Restore soundest params ...\n";
        oxRegistry::getConfig()->saveShopConfVar('str', 'sSoundestRegistered', true, null, 'module:soundest');
        oxRegistry::getConfig()->saveShopConfVar('str', 'sSoundestApiKey', 'b3eb3e1cb9d1ceac', null, 'module:soundest');

        echo "Done. (" . t() . ")\n\n";
        break;

    // Set OXID permissions.
    case 'permissions':
    case 'permisions':
        echo "Changing permissions ...\n";
        `chmod -R 0777 tmp log export out/pictures out/media out/upload`;
        `chmod -w config.inc.php .htaccess`;
        `chmod +x console.php`;
        echo "Done. (" . t() . ")\n\n";
        break;
    case 'info':
        /** @var oxShop $oBaseShop */
        $oBaseShop = oxNew("oxshop");
        $oBaseShop->load(oxRegistry::getConfig()->getBaseShopId());
        echo "Shop info...\n";
        echo "Version: " . $oBaseShop->oxshops__oxversion->value . "\n";
        echo "Edition: " . $oBaseShop->oxshops__oxedition->value . "\n";
        echo "URL: " . $oBaseShop->oxshops__oxurl->value . "\n";
        echo "Email info: " . $oBaseShop->oxshops__oxinfoemail->value . "\n";
        echo "Email order: " . $oBaseShop->oxshops__oxowneremail->value . "\n";
        echo "Email owner: " . $oBaseShop->oxshops__oxowneremail->value . "\n";
        echo "Done. (" . t() . ")\n\n";
        break;

    // Displays help information.
    case 'help':
    default:
        echo "\nUsage: php console.php <commands>\n\n";

        echo "  db:views                                  Regenerated database views\n";
        echo "  db:delete                                 Delete database\n";
        echo "  cache:language                            Clears language cache\n";
        echo "  cache:all                                 Clear all OXID cached data\n";
        echo "  cache:log                                 Clear EXEPTION_LOG.txt file\n";
        echo "  module:enable <module_id>                 Enable module with provided ID\n";
        echo "  module:disable <module_id>                Disable module with provided ID\n";
        echo "  module:info                               Show modules info\n";
        echo "  config:get <name>                         Load and display config parameter from database\n";
        echo "  config:set <name> <value> [type] [module] Set new value for config parameter\n";
        echo "                                            Type (optional): bool|str|aarr|arr|select|int\n";
        echo "                                            Module (optional): module:example|theme:example\n";
        echo "  user:change:password <username> <pass>    Change user password\n";
        echo "  user:change:rights <username> <rights>    Change user rights (user|malladmin)\n";
        echo "  user:change:country <username> <country>  Change user country (lt)\n";
        echo "  currency <currencyname>                   Change currency (ngn|eur)\n";
        echo "  permissions                               Runs chmod command to change OXID permissions\n";
        echo "  info                                      Show Oxid eShop info\n";
        echo "  soundest:reset                            Soundest params reset\n";

        echo "\n\n";
        break;
}