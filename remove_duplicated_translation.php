<?php
header("Content-Type: text/html; charset=utf-8");

ini_set("display_errors", 1);
error_reporting(E_ALL ^E_NOTICE);

$sFilePath = realpath(rtrim(__DIR__, "/\\")) . '/modules/shopcustom/before-bootstrap.php';
if(file_exists($sFilePath)) {
	require_once $sFilePath;
}

require_once dirname(__FILE__) . "/bootstrap.php";


class TranslationsUpdate extends Translations_oxLang{

	const REMOVE_DUPLICATION_ON_ALL_LANG_FILES = false;

	public function start()
	{
		$aLanguages = oxRegistry::getLang()->getLanguageArray();

		foreach($aLanguages as $oLanguage) {

			if(self::REMOVE_DUPLICATION_ON_ALL_LANG_FILES) {
				$aLangPaths = $this->_getLangFilesPathArray($oLanguage->id);
			} else { //removes duplication only in themes
				$aLangPaths = $this->getThemesLangFilesPathArray($oLanguage);
			}

			if(!empty($aLangPaths)) {
				foreach($aLangPaths as $slangPath) {
					if(!strpos($slangPath, 'translit_lang') && file_exists($slangPath)) {
						$this->removeDuplicatedTranslations($slangPath, $oLanguage);
					}
				}
			}
		}
		echo 'ok';
	}

	/**
	 * Returns Themes languages files array
	 * @param $oLanguage
	 * @return array
	 */
	private function getThemesLangFilesPathArray($oLanguage)
	{
		$aLangPaths = array();
		$aThemes = array('large', 'minimal', 'parduotuve');
		$oConfig = $this->getConfig();
		$sLang = oxRegistry::getLang()->getLanguageAbbr($oLanguage->id);

		foreach($aThemes as $sTheme) {
			$aLangPaths[] = $oConfig->getAppDir() . 'views/' . $sTheme .'/' . $sLang . "/lang.php";;
		}

		return $aLangPaths;
	}

	/**
	 * Includes translation file
	 * @param $path
	 * @param $oLanguage
	 * @throws oxException
	 */
	private function removeDuplicatedTranslations($path, $oLanguage) {

		include_once $path;

		$this->_updateTranslateFile($path, $oLanguage, $aLang);
	}

	/**
	 * Generates php code for language file.
	 *
	 * @param stdClass $oLanguage
	 * @param array    $aLanguageArray
	 *
	 * @return string
	 */
	protected function _getPhpCode($oLanguage, $aLanguageArray)
	{
		$sPhpCode = "<?php\n\n"
			. "\$sLangName = '{$oLanguage->name}';\n\n"
			. "\$aLang = array(\n";

		foreach ($aLanguageArray as $sIdent => $sValue) {
			$sValue = str_replace("'", "\\'", $sValue);
			$sPhpCode .= "    '{$sIdent}' => '$sValue',\n";
		}

		return $sPhpCode . ");";
	}

	/**
	 * Update translation file
	 *
	 * @param $sFilename string
	 * @param $oLanguage stdClass
	 * @param $aNewTranslate array
	 * @param bool $bAdmin bool
	 * @throws oxException
	 */
	protected function _updateTranslateFile($sFilename, $oLanguage, $aNewTranslates, $bAdmin = false, $bSaveOldLangFile = false)
	{
		if(file_exists($sFilename) && $bSaveOldLangFile) {
			copy($sFilename, $sFilename.'_old');
		}

		$iWrited = true;
		if (!empty($aNewTranslates)) {
			$iWrited = @file_put_contents($sFilename, $this->_getPhpCode($oLanguage, $aNewTranslates));
		}

		if (!$iWrited) {
			/** @var oxException $oEx */
			$oEx = oxNew('oxException', 'TRANSLATIONS_EXCEPTION_WRITE_ERROR');
			throw $oEx;
		}
	}
}

	$lang = oxNew('TranslationsUpdate');
	$lang->start();