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
	var $prefixId      = 'tx_nkwgmaps_pi3';
	var $scriptRelPath = 'pi3/class.tx_nkwgmaps_pi3.php';
	var $extKey        = 'nkwgmaps';
	var $pi_checkCHash = true;
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexform();
		$lang = $this->getLanguage();

                // flexform values - ui options
		$conf['ff'] = array(
			'mapName' => md5(microtime()),
			# UI Options
			'maptypeid' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypeid', 'uioptions'),
			'maptypecontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'maptypecontrol', 'uioptions'),
			'mapcenterbutton' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mapcenterbutton', 'uioptions'),
			'navicontrol' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'navicontrol', 'uioptions'),
			'scale' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'scale', 'uioptions'),
			'sensor' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sensor', 'uioptions'),
			'zoom' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'zoom', 'uioptions'),
			# Addresses
			'display' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'display', 'addresses'), // which function
			'popupoptions' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'popupdisplay', 'addresses'),
			# Single Address Options
			'singleaddress' => $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'singleaddress', 'singleaddressoptions'),	// address string
			'singleaddresspopup' => $this->pi_getFFvalue(
				$this->cObj->data['pi_flexform'], 'singleaddresspopup', 'singleaddressoptions'), // address popup content
			# Directions Options
			'start' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fromaddress', 'directionoptions'), // directions: start address
			'end' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'toaddress', 'directionoptions'), // directions: end address
			'travelmode' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'travelmode', 'directionoptions'), // directions: kind of traveling
			'directions' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'directions', 'directionoptions'), // directions: show / hide
		);
                # Address Book Options
		$conf['ff']['addressbooksource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'addressbookoptions');
                # Multi Address Options
		$conf['ff']['addressbooksource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'multiaddressbookoptions');
		# Address Group Options
		$conf['ff']['addressgroupsource']['uid'] = $this->pi_getFFvalue(
			$this->cObj->data['pi_flexform'], 'source', 'addressgroupoptions');		

                // Single
		if ($conf['ff']['display'] == 'single') {
			// get latlon
			if ($conf['ff']['singleaddress']) {
				$geo = $this->geocodeAddress($conf['ff']['singleaddress']);
				if ($geo['status'] == 'OK') {
					$conf['ff']['popupcontent'] = $conf['ff']['singleaddresspopup'];
					$conf['ff']['latlon'] = $geo['results'][0]['geometry']['location']['lat'] . ',' 
					. $geo['results'][0]['geometry']['location']['lng'];
				} else {
					$msg = 'fail. could not resolve address';
					$fail = TRUE;
				}
			} else {
				$msg = 'No address given!';
				$fail = TRUE;
			}

                // Addressbook: Single
		} else if ($conf['ff']['display'] == 'addressbook') {
			if ($conf['ff']['addressbooksource']) {
				// get data from DB
				$res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*', 
					'tt_address', 
					'uid = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($conf['ff']['addressbooksource']['uid'], 'tt_address'), 
					'', 
					'', 
					'');
				while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0)) {
					$conf['ff']['address'] = $row0['address'] . ', ' . $row0['zip'] . ' ' . $row0['city'] . ', ' 
						. $row0['country'];
					$conf['ff']['popupcontent'] = $row0['last_name'];
					if($row0['first_name']) {
						$conf['ff']['popupcontent'] = $row0['first_name'] . ' ' . $row0['last_name'];
					}
					$conf['ff']['latlon'] = $row0['tx_dsschedgmaps_geocodecache'];
				}
				if($conf['ff']['latlon'] != 'undefined' && !empty($conf['ff']['latlon']))	{
					;
				} else {
					$geo = $this->geocodeAddress($conf['ff']['address']);
					if ($geo['status'] == 'OK')	{
						$conf['ff']['latlon'] = $geo['results'][0]['geometry']['location']['lat'] . ',' 
							. $geo['results'][0]['geometry']['location']['lng'];
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
                } else if ($conf['ff']['display'] == 'addressgroup' || $conf['ff']['display'] == 'multiaddressbook') {
			if ($conf['ff']['addressgroupsource']['uid']) {
				$cntMarker = 0;
				// get data from DB
                                $conf['ff']['addressbooksource']['search'] = array();
				if($conf['ff']['display'] == 'addressgroup')    {
                                    $res0 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                                            'first_name, last_name, address, zip, city, country, tx_dsschedgmaps_geocodecache',
                                            'tt_address', 'tt_address_group_mm', 'tt_address_group',
                                            " AND uid_foreign = " . $conf['ff']['addressgroupsource']['uid'], //
                                            '', '', ''
                                            );
                                }   else    {
                                    $res0 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                            '*',
                                            'tt_address',
                                            ' uid IN (' . $conf['ff']['addressbooksource']['uid'] . ')',
                                            '',
                                            '',
                                            '');
                                }

				while($row0 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res0)) {
                                        # t3lib_div::print_array($row0);
					$conf[$cntMarker]['address'] = $row0['address'] . ', ' . $row0['zip'] . ' ' . $row0['city'] . ', ' 
						. $row0['country'];
					$conf[$cntMarker]['popupcontent'] = $row0['last_name'];
					if ($row0['first_name']) {
						$conf[$cntMarker]['popupcontent'] = $row0['first_name'] . ' ' . $row0['last_name'];
					}
					$conf[$cntMarker]['latlng'] = $row0['tx_dsschedgmaps_geocodecache'];
					if($conf[$cntMarker]['latlng'] != 'undefined' && !empty($conf[$cntMarker]['latlng'])) {
						$geo = explode(',', $conf[$cntMarker]['latlng']);
						$lat[$cntMarker] = $geo[0];
						$lng[$cntMarker] = $geo[1];
					} else {
						$geo = $this->geocodeAddress($conf[$cntMarker]['address']);
						if ($geo['status'] == 'OK')	{
							$conf[$cntMarker]['latlng'] = $geo['results'][0]['geometry']['location']['lat'] . ',' 
								. $geo['results'][0]['geometry']['location']['lng'];
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
				if($cntMarker > 0) {
					/* calculate center-position of the map */ 
					$borders = array('l' => 180, 'r' => -180, 'b' => 90, 't' => -90);
					for($i = 0; $i < $cntMarker; $i++) {
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
					}	else {
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
		} else if ($conf['ff']['display'] == 'directions') {
			// get latlon
			if ($conf['ff']['start'] && $conf['ff']['end']) {
				$geoStart = $this->geocodeAddress($conf['ff']['start']);
				$geoEnd = $this->geocodeAddress($conf['ff']['end']);
				if ($geoStart['status'] == "OK" && $geoEnd['status'] == 'OK') {
					$latMean = round(($geoStart['results'][0]['geometry']['location']['lat'] 
						+ $geoEnd['results'][0]['geometry']['location']['lat']) / 2,5);
					$lngMean = round(($geoStart['results'][0]['geometry']['location']['lng'] 
						+ $geoEnd['results'][0]['geometry']['location']['lng']) / 2,5);
					$conf['ff']['latlngCenter'] = $latMean . ',' . $lngMean;
				} else {
					$conf['ff']['latlngCenter'] = "51.53290, 9.93496"; // Gaenseliesl
				} 
			} else {
				$msg = 'No address given!';
				$fail = TRUE;
			}
		}

                if (!$fail) {
			// the div-container in which the map is displayed
			$tmp = '<div id="' . $conf['ff']['mapName'] . '" class="tx-nkwgmaps-border"></div>';
			switch($conf['ff']['display']) {
				case 'single':		;
				case 'addressbook':	 $js = $this->singleGmapsJS($conf); break;
				case 'multiaddressbook': $js = $this->multiGmapsJS($conf); break;
				case 'addressgroup':     $js = $this->multiGmapsJS($conf); break;
				case 'directions':
                                    if($conf['ff']['directions'] == "true") {
                                        $js = $this->directionsWithSteps($conf);
                                        $tmp .= '<div id="directionsPanel" class="tx-nkwgmaps-border tx-nkwgmaps-directionspanel"></div>';
                                    }  else
                                        $js = $this->directions($conf);
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