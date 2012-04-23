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
require_once(t3lib_extMgm::extPath('nkwgmaps') . 'lib/class.tx_nkwgmaps.php');
/**
 * Plugin 'Address Group Map' for the 'nkwgmaps' extension.
 *
 * @author	Nils K. Windisch <windisch@sub.uni-goettingen.de>
 * @package	TYPO3
 * @subpackage	tx_nkwgmaps
 */
class tx_nkwgmaps_pi3 extends tx_nkwgmaps {
	public $prefixId = 'tx_nkwgmaps_pi3';
	public $scriptRelPath = 'pi3/class.tx_nkwgmaps_pi3.php';
	public $extKey  = 'nkwgmaps';
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

			// flexform values - ui options
		$conf['ff'] = array(
			'mapName' => md5(microtime()),
				// UI Options
			'maptypeid' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'),
			'maptypecontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'),
			'mapcenterbutton' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions'),
			'navicontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'),
			'scale' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'),
			'sensor' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'),
			'zoom' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'),
				// Addresses
				// which function
			'display' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'display', 'addresses'),
			'popupoptions' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupdisplay', 'addresses'),
				// Single Address Options
				// address string
			'singleaddress' => $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'singleaddress', 'singleaddressoptions'),
				// address popup content
			'singleaddresspopup' => $this->pi_getFFvalue(
					// address popup content
				$this->cObj->data['pi_flexform'], 'singleaddresspopup', 'singleaddressoptions'),
					// Directions Options
					// directions: start address
				'start' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fromaddress', 'directionoptions'),
					// directions: end address
				'end' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toaddress', 'directionoptions'),
					// directions form: map center and to address
				'latlngCenter' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenteraddress', 'directionformoptions'),
					// directions form: popup content to address
				'directionformpopup' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'directionformpopup', 'directionformoptions'),
					// directions: kind of traveling
				'travelmode' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'travelmode', 'directionoptions'),
					// directions: show / hide
				'directionsvisibility' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'directionsvisibility', 'directionoptions'),
					// directions: show / hide
				'directionsformvisibility' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'directionsformvisibility', 'directionformoptions'),
		);

			// Address Book Options
		$conf['ff']['addressbooksource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'addressbookoptions');
			// Multi Address Options
		$conf['ff']['addressbooksource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'multiaddressbookoptions');
			// Address Group Options
		$conf['ff']['addressgroupsource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'addressgroupoptions');

			// Single
		if ($conf['ff']['display'] == 'singleaddress') {
				// get latlon
			if ($conf['ff']['singleaddress']) {
				$geo = tx_nkwlib::geocodeAddress($conf['ff']['singleaddress']);
				if ($geo['status'] == 'OK') {
					$conf['ff']['popupcontent'] = $conf['ff']['singleaddresspopup'];
					$conf['ff']['latlon'] = $geo['results'][0]['geometry']['location']['lat'] . ',' . $geo['results'][0]['geometry']['location']['lng'];
				} else {
					$msg = 'fail. could not resolve address';
					$fail = TRUE;
				}
			} else {
				$msg = 'No address given!';
				$fail = TRUE;
			}

			// Addressbook: Single
		} elseif ($conf['ff']['display'] == 'addressbook') {
			if ($conf['ff']['addressbooksource']) {
					// get data from DB
				$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tt_address',
					'uid = ' . $conf['ff']['addressbooksource']['uid'],
					'',
					'',
					'');
				while ($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0)) {
					$conf['ff']['address'] = $row0['address'] . ', ' . $row0['zip'] . ' ' . $row0['city'] . ', ' . $row0['country'];
					$conf['ff']['popupcontent'] = $row0['last_name'];
					if ($row0['first_name']) {
						$conf['ff']['popupcontent'] = $row0['first_name'] . ' ' . $row0['last_name'];
					}
					$conf['ff']['latlon'] = $row0['tx_dsschedgmaps_geocodecache'];
				}
				if ($conf['ff']['latlon'] != 'undefined' && !empty($conf['ff']['latlon'])) {
					// null
				} else {
					$geo = tx_nkwlib::geocodeAddress($conf['ff']['address']);
					if ($geo['status'] == 'OK') {
						$conf['ff']['latlon'] = $geo['results'][0]['geometry']['location']['lat'] . ',' . $geo['results'][0]['geometry']['location']['lng'];
					} else {
						$msg = 'Fail. Could not resolve address';
						$fail = TRUE;
					}
				}
			} else {
				$msg = 'No address given!';
				$fail = TRUE;
			}

				// Addressbook and Addressgroup
			} elseif ($conf['ff']['display'] == 'addressgroup' || $conf['ff']['display'] == 'multiaddressbook') {
			if ($conf['ff']['addressgroupsource']['uid']) {
				$cntMarker = 0;
					// get data from DB
				$conf['ff']['addressbooksource']['search'] = array();
				if ($conf['ff']['display'] == 'addressgroup') {
					$res0 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						'first_name, last_name, address, zip, city, country, tx_dsschedgmaps_geocodecache',
						'tt_address', 'tt_address_group_mm', 'tt_address_group',
						' AND uid_foreign = ' . $conf['ff']['addressgroupsource']['uid'],
						'',
						'',
						''
					);
				} else {
					$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						'tt_address',
						' uid IN (' . $conf['ff']['addressbooksource']['uid'] . ')',
						'',
						'',
						''
					);
				}

				while ($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0)) {
					$conf[$cntMarker]['address'] = $row0['address'] . ', ' . $row0['zip'] . ' ' . $row0['city'] . ', ' . $row0['country'];
					$conf[$cntMarker]['popupcontent'] = $row0['last_name'];
					if ($row0['first_name']) {
						$conf[$cntMarker]['popupcontent'] = $row0['first_name'] . ' ' . $row0['last_name'];
					}
					$conf[$cntMarker]['latlng'] = $row0['tx_dsschedgmaps_geocodecache'];
					if ($conf[$cntMarker]['latlng'] != 'undefined' && !empty($conf[$cntMarker]['latlng'])) {
						$geo = explode(',', $conf[$cntMarker]['latlng']);
						$lat[$cntMarker] = $geo[0];
						$lng[$cntMarker] = $geo[1];
					} else {
						$geo = tx_nkwlib::geocodeAddress($conf[$cntMarker]['address']);
						if ($geo['status'] == 'OK') {
							$conf[$cntMarker]['latlng'] = $geo['results'][0]['geometry']['location']['lat'] . ',' . $geo['results'][0]['geometry']['location']['lng'];
							$lat[$cntMarker] = $geo['results'][0]['geometry']['location']['lat'];
							$lng[$cntMarker] = $geo['results'][0]['geometry']['location']['lng'];
						} else {
							$msg = 'Fail. Could not resolve address!';
							$fail = TRUE;
						}
					}
					$cntMarker++;
				}
				$conf['ff']['cntMarker'] = $cntMarker;
				if ($cntMarker > 0) {
						// calculate center-position of the map
					$borders = array('l' => 180, 'r' => -180, 'b' => 90, 't' => -90);
					for ($i = 0; $i < $cntMarker; $i++) {
						if ($lat[$i] < $borders['l']) {
							$borders['l'] = $lat[$i];
						}
						if ($lat[$i] > $borders['r']) {
							$borders['r'] = $lat[$i];
						}
						if ($lng[$i] < $borders['b']) {
							$borders['b'] = $lng[$i];
						}
						if ($lng[$i] > $borders['t']) {
							$borders['t'] = $lng[$i];
						}
					}
					if ($cntMarker > 1) {
						$latMean = round(($borders['l'] + $borders['r']) / 2,5);
						$lngMean = round(($borders['b'] + $borders['t']) / 2,5);
					} else {
						$latMean = $lat[0];
						$lngMean = $lng[0];
					}
					$conf['ff']['latlngCenter'] = $latMean . ',' . $lngMean;
				} else {
					$msg = 'No members in given group!';
					$fail = TRUE;
				}
			} else {
				$msg = 'No address group given!';
				$fail = TRUE;
			}

			// Directions
		} elseif ($conf['ff']['display'] == 'directions') {
				// get latlon
			if ($conf['ff']['start'] && $conf['ff']['end']) {
				$geoStart = tx_nkwlib::geocodeAddress($conf['ff']['start']);
				$geoEnd = tx_nkwlib::geocodeAddress($conf['ff']['end']);
				if ($geoStart['status'] == "OK" && $geoEnd['status'] == 'OK') {
					$latMean = round(($geoStart['results'][0]['geometry']['location']['lat'] + $geoEnd['results'][0]['geometry']['location']['lat']) / 2,5);
					$lngMean = round(($geoStart['results'][0]['geometry']['location']['lng'] + $geoEnd['results'][0]['geometry']['location']['lng']) / 2,5);
					$conf['ff']['latlngCenter'] = $latMean . ',' . $lngMean;
				} else {
						// Gaenseliesel
					$conf['ff']['latlngCenter'] = '51.53290, 9.93496';
				}
			} else {
				$msg = 'No address given!';
				$fail = TRUE;
			}

		} elseif ($conf['ff']['display'] == 'directionsform') {
				// get latlon
			if ($_REQUEST['startpoint'] && $_REQUEST['endpoint'] && $_REQUEST['travelmode']) {
				$formURL = $this->pi_linkTP_keepPIvars_url($config, $cache=0, $clearAnyway=1, $altPageId=0);

				$form = '
					<div id="'. $this->prefixId .'_directions_form_back">
					<form id="nkwgmaps_directions_form" action="' . $formURL . '" method="POST">
					<input type="submit" name="submit" value="' . $this->pi_getLL("back") . '">
					</form>
					</div>';

				$conf['ff']['start'] = $_REQUEST['startpoint'];
				$conf['ff']['end'] = $_REQUEST['endpoint'];
				$conf['ff']['travelmode'] = $_REQUEST['travelmode'];
				$geoStart = tx_nkwlib::geocodeAddress($conf['ff']['start']);
				$geoEnd = tx_nkwlib::geocodeAddress($conf['ff']['end']);

				if ($geoStart['status'] == 'OK') {
					$latMean = round(($geoStart['results'][0]['geometry']['location']['lat'] + $geoEnd['results'][0]['geometry']['location']['lat']) / 2, 5);
					$lngMean = round(($geoStart['results'][0]['geometry']['location']['lng'] + $geoEnd['results'][0]['geometry']['location']['lng']) / 2, 5);
					$conf['ff']['latlngCenter'] = $latMean . ',' . $lngMean;
				} else {
						// Gaenseliesel (Stadtmitte)
					$conf['ff']['latlngCenter'] = '51.53290, 9.93496';
				}
			} else {
				$geoPos = tx_nkwlib::geocodeAddress($conf['ff']['latlngCenter']);
					// get db-stored geoPos, if it exists
				$address = explode(",",$conf['ff']['latlngCenter']);
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'DISTINCT `tx_dsschedgmaps_geocodecache`',
					'tt_address',
					'address = ' . "'" . trim($address[0]) . "' AND `tx_dsschedgmaps_geocodecache` != 'undefined'" . $GLOBALS['TSFE']->sys_page->enableFields('tt_address'),
					'',
					'',
					'1');
				if($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0)	{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$geoCoord = explode(",",$row['tx_dsschedgmaps_geocodecache']);
					$geoPos['results'][0]['geometry']['location']['lat'] = $geoCoord[0];
					$geoPos['results'][0]['geometry']['location']['lng'] = $geoCoord[1];
				}
				
				if ($geoPos['status'] == 'OK') {
					$conf['ff']['latlon'] = $geoPos['results'][0]['geometry']['location']['lat'] . ',' . $geoPos['results'][0]['geometry']['location']['lng'];
				} else {
						// Gaenseliesl (Stadtmitte)
					$conf['ff']['latlon'] = '51.53290, 9.93496';
					}

					$conf['ff']['popupcontent'] = $conf['ff']['directionformpopup'];
					$config = array(
						'parameter' => $GLOBALS['TSFE']->id,
						'useCacheHash' => TRUE,
						'additionalParams' => ''
					);
					$formURL = $this->pi_linkTP_keepPIvars_url($config, $cache = 0, $clearAnyway = 1, $altPageId = 0);

					$form = '
                                            <div id="'. $this->prefixId .'_directions_form">
                                            <form id="nkwgmaps_directions_form" action="' . $formURL . '" method="POST">
                                            <fieldset>
                                            <legend>' . $this->pi_getLL('directions') . '</legend>
                                            <dl>
                                                    <dt>' . $this->pi_getLL('fromaddress') . '</dt><dd><input id="startpoint" name="startpoint" type="text" size="44" value="' . $this->pi_getLL('addressformat') . '" onFocus="javascript:this.value=\'\'; this.style.color=\'#000\';" /></dd>
                                                    <dt>' . $this->pi_getLL('toaddress') . '</dt><dd><input id="endpoint" name="endpoint" type="text" size="44" value="' . $conf['ff']['latlngCenter'] . '" readonly="readonly" /></dd>
                                                    <dt>' . $this->pi_getLL('travelmode') .'</dt><dd><select name="travelmode">
                                                            <option value="DRIVING">' . $this->pi_getLL('travelmode.I.1') .'</option>
                                                            <option value="WALKING">' . $this->pi_getLL('travelmode.I.0') .'</option>
                                                            <!--<option value="BICYCLING">' . $this->pi_getLL('travelmode.I.2') .'</option>-->
                                                    </select>
                                                    </dd>
                                                    <dt></dt><dd class="submit"><input type="submit" name="submit" value="' . $this->pi_getLL('send') . '"></dd>
                                            </dl>
                                            </fieldset>
                                            </form>
                                            </div>';
                                    }
                            }

		if (!$fail) {
			// the div-container in which the map is displayed
			$tmp = '<div id="' . $conf['ff']['mapName'] . '" class="tx-nkwgmaps-border"></div>';
			switch ($conf['ff']['display']) {
				case 'singleaddress':;
				case 'addressbook':	 $js = tx_nkwgmaps::singleGmapsJS($conf);
					break;
				case 'multiaddressbook': $js = tx_nkwgmaps::multiGmapsJS($conf);
					break;
				case 'addressgroup':	 $js = tx_nkwgmaps::multiGmapsJS($conf);
					break;
				case 'directions':
					if ($conf['ff']['directionsvisibility'] == 'true') {
						$js = tx_nkwgmaps::directionsWithSteps($conf);
						$tmp .= '<div id="directionsPanel" class="tx-nkwgmaps-border tx-nkwgmaps-directionspanel"></div>';
					} else {
						$js = tx_nkwgmaps::directions($conf);
					}
					break;
				case 'directionsform':
					if (!isset($_REQUEST['startpoint']) && !isset($_REQUEST['endpoint'])) {
						$tmp .= $form;
						$js = tx_nkwgmaps::showGMap($conf);
					} else {
						if ($conf['ff']['directionsformvisibility'] == 'true') {
							$js = tx_nkwgmaps::directionsWithSteps($conf);
							$tmp .= '<div id="directionsHint"><strong>' . $this->pi_getLL('caution') . '</strong> ' . $this->pi_getLL('hint') . '</div>';
                                                        $tmp .= '<div id="directionsPanel" class="tx-nkwgmaps-directionspanel"></div>';
                                                        $tmp .= $form;
						} else {
							$js = tx_nkwgmaps::directions($conf);
						}
					}
					break;
			}
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi3/class.tx_nkwgmaps_pi3.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nkwgmaps/pi3/class.tx_nkwgmaps_pi3.php']);
}
?>