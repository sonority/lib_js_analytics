<?php

namespace Sonority\LibJsAnalytics\Slot\Extensionmanager;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Remove entries from 'extConf' and delete uploads-folder after uninstalling this extension.
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
class Uninstall implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Execute signalSlot 'afterExtensionUninstall'
     *
     * @param string $extensionName
     */
    public static function executeOnSignal($extensionName = null)
    {
        if ($extensionName !== 'lib_js_analytics') {
            return;
        }
        // Cleanup uploads-folder (containing downloaded analytics.js) and extension-configuration
        GeneralUtility::rmdir(GeneralUtility::getFileAbsFileName('uploads/tx_libjsanalytics/'), true);
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
        $configurationManager->removeLocalConfigurationKeysByPath(['EXT/extConf/lib_js_analytics']);
    }

}
