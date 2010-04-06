<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:nkwgmaps/pi1/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:nkwgmaps/pi2/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:nkwgmaps/pi2/flexform.xml');

t3lib_extMgm::addPlugin(array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi1',$_EXTKEY . '_pi1',t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi2',$_EXTKEY . '_pi2',t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:nkwgmaps/locallang_db.xml:tt_content.list_type_pi3',$_EXTKEY . '_pi3',t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'),'list_type');
?>