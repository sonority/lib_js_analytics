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

use Sonority\LibJsAnalytics\Tools;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Download analytics.js after installing this extension.
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
class Install implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Execute signalSlot 'hasInstalledExtensions'
     *
     * @param string $extensionName
     */
    public static function executeOnSignal($extensionName = null)
    {
        return;
        /*
          if ($extensionName != 'lib_js_analytics' && !ExtensionManagementUtility::isLoaded($extensionName)) {
          return;
          }
          Tools::updateJavaScript();
         */
    }

}
