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
 * Plugin 'Multi Address Entry Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi4 extends tx_nkwgmaps {
	var $prefixId      = 'tx_nkwgmaps_pi4';
	var $scriptRelPath = 'pi4/class.tx_nkwgmaps_pi4.php';
	var $extKey        = 'nkwgmaps';
	var $pi_checkCHash = true;
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$lang = $this->getLanguage();
		// flexform values - ui options 
		$conf['ff'] = array(
			'mapName' => md5(microtime()),
			'maptypeid' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'),
			'maptypecontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'),
			'mapcenterbutton' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions'),
			'navicontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'),
			'scale' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'),
			'sensor' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'),
			'zoom' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'),
			'popupoptions' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupoptions', 'addressdata')
		);
		$conf['ff']['addressbooksource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'addressdata');
		// get data from DB
		$cntMarker = 0;
		$addressbooksource_uids = explode(',', $conf['ff']['addressbooksource']['uid']);
		foreach($addressbooksource_uids as $uid) {
			$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*', 
				'tt_address', 
				'uid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, 'tt_address'), 
				'', 
				'', 
				'');
			while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0)) {
				$conf[$cntMarker]['address'] = $row0['address'] . ', ' . $row0['zip'] . ' ' . $row0['city'] 
					. ', ' . $row0['country'];
				$conf[$cntMarker]['popupcontent'] = $row0['last_name'];
				if ($row0['first_name']) {
					$conf[$cntMarker]['popupcontent'] = $row0['first_name'] . ' ' . $row0['last_name'];
				}
				$conf[$cntMarker]['latlng'] = $row0['tx_dsschedgmaps_geocodecache'];
			}

			if($conf[$cntMarker]['latlng'] != 'undefined' && !empty($conf[$cntMarker]['latlng'])) {
				$geo = explode(',', $conf[$cntMarker]['latlng']);
				$lat[$cntMarker] = $geo[0];
				$lng[$cntMarker] = $geo[1];
			} else {
				$geo = $this->geocodeAddress($conf[$cntMarker]['address']);
				if ($geo['status'] == 'OK') {
					$conf[$cntMarker]['latlng'] = $geo['results'][0]['geometry']['location']['lat'] . ',' 
						. $geo['results'][0]['geometry']['location']['lng'];
					$lat[$cntMarker] = $geo['results'][0]['geometry']['location']['lat'];
					$lng[$cntMarker] = $geo['results'][0]['geometry']['location']['lng'];
				} else {
					$msg = 'fail. could not resolve address';
					$fail = TRUE;
				}
			}
			$cntMarker++;
		}
		$conf['ff']['cntMarker'] = $cntMarker;
		/* calculate center-position of the map */ 
		$borders = array('l' => 180, 'r' => -180, 'b' => 90, 't' => -90);
		for($i=0; $i<$cntMarker; $i++) {
			if($lat[$i] < $borders['l']) {
				$borders['l'] = $lat[$i];
			}
			if($lat[$i] > $borders['r']) {
				$borders['r'] = $lat[$i];
			}
			if($lng[$i] < $borders['b']) {
				$borders['b'] = $lng[$i];
			}
			if($lng[$i] > $borders['t']) {
				$borders['t'] = $lng[$i];
			}
		}
		if($cntMarker > 1) {
			$latMean = round(($borders['l']+$borders['r'])/2,5);
			$lngMean = round(($borders['b']+$borders['t'])/2,5);
		} else {
			$latMean = $lat[0];
			$lngMean = $lng[0];
		}
		$conf['ff']['latlngCenter'] = $latMean . ',' . $lngMean;
		if (!$fail) {
			// the div in which the map is displayed
			$tmp = '<div id="' . $conf['ff']['mapName'] . '" class="tx-nkwgmaps-border"></div>';
			$js = $this->multiGmapsJS($conf);
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi4/class.tx_nkwgmaps_pi4.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi4/class.tx_nkwgmaps_pi4.php']);
}
?>