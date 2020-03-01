<?php

defined('TYPO3_MODE') or die();

// Define TypoScript as content rendering template
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'lib_js_analytics/Configuration/TypoScript/';

if (TYPO3_MODE === 'FE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) .
        'Classes/Hooks/PageRenderer.php:Sonority\\LibJsAnalytics\\Hooks\\PageRenderer->renderPreProcess';
}
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Sonority\\LibJsAnalytics\\Task\\Update'] = [
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:lib_js_analytics/Resources/Private/Language/locallang.xlf:task.update.title',
    'description' => 'LLL:EXT:lib_js_analytics/Resources/Private/Language/locallang.xlf:task.update.description',
];

// Make a call to update
if (TYPO3_MODE === 'BE') {
    $class = 'TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher';
    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class);
    $dispatcher->connect(
        'TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService',
        'hasInstalledExtensions',
        'Sonority\\LibJsAnalytics\\Slot\\Extensionmanager\\Install',
        'executeOnSignal'
    );
    $dispatcher->connect(
        'TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility',
        'afterExtensionUninstall',
        'Sonority\\LibJsAnalytics\\Slot\\Extensionmanager\\Uninstall',
        'executeOnSignal'
    );
}
