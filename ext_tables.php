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
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi2'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi3'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi4'] = 'layout,select_key,pages';
	// add flexforms
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:nkwgmaps/pi1/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi2'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi2', 'FILE:EXT:nkwgmaps/pi2/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi3'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi3', 'FILE:EXT:nkwgmaps/pi3/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi4'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi4', 'FILE:EXT:nkwgmaps/pi4/flexform.xml');
	// add plugins
t3lib_extMgm::addPlugin(
	array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY . '_pi1', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'), 'list_type');
t3lib_extMgm::addPlugin(
	array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY . '_pi2', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'), 'list_type');
t3lib_extMgm::addPlugin(
	array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY . '_pi3', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'), 'list_type');
t3lib_extMgm::addPlugin(
	array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY . '_pi4', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'), 'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY, 'pi3/static/', 'GMapsPi3');

?>