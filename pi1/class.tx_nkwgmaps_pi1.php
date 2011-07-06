<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils K. Windisch <windisch@sub.uni-goettingen.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
require_once(t3lib_extMgm::extPath('nkwlib') . 'class.tx_nkwlib.php');
require_once(t3lib_extMgm::extPath('nkwgmaps') . 'lib/class.tx_nkwgmaps.php');

/**
 * Plugin 'Simple Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi1 extends tx_nkwgmaps {
	public $prefixId = 'tx_nkwgmaps_pi1';
	public $scriptRelPath = 'pi1/class.tx_nkwgmaps_pi1.php';
	public $extKey = 'nkwgmaps';
	public $pi_checkCHash = TRUE;
	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content The PlugIn content
	 * @param array $conf The PlugIn configuration
	 * @return The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$lang = tx_nkwlib::getLanguage();
		$this->pi_USER_INT_obj = 0;

		$conf['ff'] = array(
			'mapName' => md5(microtime()),
			'maptypeid' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'),
			'maptypecontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'),
			'mapcenterbutton' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions'),
			'navicontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'),
			'scale' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'),
			'sensor' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'),
			'zoom' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'),
			'popupoptions' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupoptions', 'addressdata'),
			'address' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'address', 'addressdata'),
			'popupcontent' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupcontent', 'addressdata')
		);
		$conf['ff']['mapName'] = md5($conf['ff']['address']);
			// get latlon
		$geo = tx_nkwlib::geocodeAddress($conf['ff']['address']);
		if ($geo['status'] == 'OK') {
			$conf['ff']['latlon'] = $geo['results'][0]['geometry']['location']['lat'] . ',' . $geo['results'][0]['geometry']['location']['lng'];
		} else {
			$msg = 'fail. could not resolve address';
			$fail = TRUE;
		}
		if (!$fail) {
				// the div in which the map is displayed
			$tmp = '<div id="' . $conf['ff']['mapName'] . '" class="tx-nkwgmaps-border"></div>';
			$js = tx_nkwgmaps::singleGmapsJS($conf);
		} else {
			$tmp = '<p>' . $msg . '</p>';
		}

		$content = $tmp;
		if (!$fail) {
			$content .= $js;
		}
		return $this->pi_wrapInBaseClass($content);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi1/class.tx_nkwgmaps_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi1/class.tx_nkwgmaps_pi1.php']);
}
?>