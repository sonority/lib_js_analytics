<?php

namespace Sonority\LibJsAnalytics\Hooks;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Add google-analytics trackingcode
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
class PageRenderer
{

    protected $cObjRenderer = null;

    /**
     * Insert javascript-tags for google-analytics
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pObj
     * @return void
     */
    public function renderPreProcess($params, $pObj)
    {
        if (TYPO3_MODE === 'FE') {
            // Get plugin-configuration
            $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_libjsanalytics.']['settings.'];
            // Exclude the analytics-snippet on some pages
            $pageExclude = GeneralUtility::trimExplode(',', $conf['pageExclude'], true);
            // Generate script-tag for google-analytics if enabled
            if ((int) $conf['enable'] && !in_array($GLOBALS['TSFE']->id, $pageExclude)) {
                $analyticsPreScript = '';
                $analyticsPostScript = '';
                // Instruct analytics.js to use the name defined in typoscript
                if (!empty($conf['gaObjectName']) && $conf['gaObjectName'] !== 'ga') {
                    $gaObjectName = $conf['gaObjectName'];
                } else {
                    $gaObjectName = 'ga';
                }
                // Set attribute for cookie-consent
                if (!empty($conf['gaCookieConsent'])) {
                    $gaCookieConsent = ' ' . htmlentities($conf['gaCookieConsent'], ENT_NOQUOTES);
                } else {
                    $gaCookieConsent = '';
                }
                // Get filePath to analytics.js
                $analyticsJavascriptFile = Tools::getConfParam('localFile');
                if ($conf['forceCdn'] || !file_exists(PATH_site . $analyticsJavascriptFile)) {
                    $analyticsJavascriptFile = Tools::getConfParam('sourceFile');
                } else {
                    // If local file is not available, fall back to CDN
                    if (empty($analyticsJavascriptFile)) {
                        $analyticsJavascriptFile = Tools::getConfParam('sourceFile');
                    } else {
                        // Prefix file with absRefPrefix if path is relative
                        if (!GeneralUtility::isAbsPath($analyticsJavascriptFile)) {
                            $analyticsJavascriptFile = $GLOBALS['TSFE']->absRefPrefix . $analyticsJavascriptFile;
                        }
                        // Append filename with version numbers
                        $analyticsJavascriptFile = GeneralUtility::createVersionNumberedFilename($analyticsJavascriptFile);
                    }
                }
                // Insert different codeblocks for different integration (old/new)
                if ((int) $conf['alternative']) {
                    if ($conf['gaObjectName'] !== 'ga') {
                        $scriptTag .= LF . 'window.GoogleAnalyticsObject = \'' . $gaObjectName . '\';';
                    }
                    // Create an initial analytics() function
                    // The queued commands will be executed once analytics.js loads
                    $scriptTag .=
                        LF . 'window.' . $gaObjectName . ' = window.' . $gaObjectName . ' || function() {' .
                        '(' . $gaObjectName . '.q = ' . $gaObjectName . '.q || []).push(arguments)};';
                    // Set the time (as an integer) this tag was executed (Used for timing hits)
                    $scriptTag .= LF . $gaObjectName . '.l =+ new Date;';
                    // Compile final script-tag for analytics.js
                    // Compile final script-tag for analytics.js
                    if (empty($gaCookieConsent)) {
                        $analyticsPostScript = LF . '<script src="' . $analyticsJavascriptFile . '" type="text/javascript" async="async"></script>';
                    } else {
                        $analyticsPostScript = LF . '<script' . $gaCookieConsent . ' data-src="' . $analyticsJavascriptFile . '" type="text/plain" async="async"></script>';
                    }
                } else {
                    // Compile final script-tag for analytics.js
                    $analyticsPreScript = LF . '(function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){' .
                        LF . '(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),' .
                        LF . 'm=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)' .
                        LF . '})(window,document,\'script\',\'' . $analyticsJavascriptFile . '\',\'' . $gaObjectName . '\');';
                }
                // Create the tracker
                foreach ($conf['gaObject.'] as $gaName => $gaTracker) {
                    // Set the name of the tracker if defined in typoscript
                    $trackerName = '';
                    if ($gaName !== 'default.') {
                        $trackerName = $gaName;
                    }
                    // Check if analytics objects are defined
                    if (is_array($gaTracker)) {
                        $scriptTag .= $this->iterateTrackerMethods($gaObjectName, $trackerName, $gaTracker);
                    }
                }
                // TEST/TODO
                // Add additional javascript to a single page if defined in PageTSConfig
                //debug($GLOBALS['TSFE']->page['TSconfig']);
                /*
                  $pageTS = BackendUtility::getPagesTSconfig($GLOBALS['TSFE']->id, [$GLOBALS['TSFE']->id]);
                  if (isset($pageTS['tx_libjsanalytics.'])) {
                  $analyticsPageTS = $pageTS['tx_libjsanalytics.'];
                  if (is_array($analyticsPageTS['additionalScript.'])) {
                  $scriptTag .= LF . $this->getCobject($analyticsPageTS['additionalScript'], $analyticsPageTS['additionalScript.']);
                  }
                  }
                 */
                // Compile final codeblock
                $inlineCode = (empty($gaCookieConsent) ? '<script type="text/javascript">' : '<script' . $gaCookieConsent . ' type="text/plain">') .
                    LF . '/*<![CDATA[*/' .
                    $analyticsPreScript .
                    $scriptTag .
                    LF . '/*]]>*/' .
                    LF . '</script>' .
                    $analyticsPostScript;
                // Add final code to HTML
                $pObj->addHeaderData($scriptTag);
            }
        }
    }

    /**
     * Iterate through ga-methods defined in 'plugin.tx_libjsanalytics.settings.TRACKERNAME.*'
     *
     * @param string $gaObjectName The name of the analytics-object
     * @param string $trackerName The optional tracker name
     * @param array $gaTracker The array containing the methods
     * @return string The final scriptTag for the ga-method
     */
    private function iterateTrackerMethods($gaObjectName, $trackerName, $gaTracker)
    {
        $scriptTag = '';
        $gaMethods = ['create', 'require', 'set', 'additionalScript', 'send'];
        while (list($key, $action) = each($gaMethods)) {
            if (is_array($gaTracker[$action . '.'])) {
                $gaObject = $gaTracker[$action . '.'];
                // Handle the analytics-methods
                if (is_array($gaObject)) {
                    switch ($action) {
                        case 'create':
                            // Add tracker-name if defined in typoscript
                            if (!empty($trackerName)) {
                                $gaObject['name'] = substr($trackerName, 0, -1);
                            } else {
                                unset($gaObject['name']);
                            }
                            $scriptTag .= $this->processGaObjects($gaObjectName, $action, $gaObject);
                            break;
                        case 'send':
                            if (is_array($gaObject) && count($gaObject) > 0) {
                                foreach ($gaObject as $hitType => $gaSendObject) {
                                    // Set/override hitType with the name of the current object
                                    $gaSendObject = ['hitType' => substr($hitType, 0, -1)] + $gaSendObject;
                                    $scriptTag .= $this->processGaObjects($gaObjectName, $trackerName . $action, $gaSendObject);
                                }
                            }
                            break;
                        case 'additionalScript':
                            // Add additional codeblocks
                            $scriptTag .= LF . $this->getCobject('COA', $gaObject);
                            break;
                        default:
                            $scriptTag .= $this->processGaObjects($gaObjectName, $trackerName . $action, $gaObject);
                            break;
                    }
                }
            }
        }
        return $scriptTag;
    }

    /**
     * Processes a ga-method and all of its childs
     *
     * @param string $gaObjectName The name of the analytics-object
     * @param string $action The current ga-method/action
     * @param array $gaObject The array containing the childs
     * @return string The final ga-method
     */
    private function processGaObjects($gaObjectName, $action, $gaObject)
    {
        $gaObjectTags = $this->getFieldsAndValues($gaObject);
        if (count($gaObjectTags) > 0) {
            return LF . $gaObjectName . '(\'' . $action . '\', {' . implode(', ', $gaObjectTags) . '});';
        }
    }

    /**
     * Iterate through fields
     *
     * @param array $gaObject The array containing the childs
     * @return array The final ga-method
     */
    private function getFieldsAndValues($gaObject)
    {
        $gaObjectTags = [];
        foreach ($gaObject as $key => $value) {
            if (!empty($value)) {
                if (in_array($value, ['TEXT', 'COA'])) {
                    $value = $this->getCobject($gaObject[$key], $gaObject[$key . '.']);
                    $gaObjectTags[] = $key . ': ' . $value;
                } elseif (substr($key, -1) !== '.') {
                    if ($gaObject[$key . '.']['type']) {
                        $value = $this->prepareValue($value, $gaObject[$key . '.']['type']);
                    } else {
                        $value = '\'' . $value . '\'';
                    }
                    $gaObjectTags[] = $key . ': ' . $value;
                }
            }
        }
        return $gaObjectTags;
    }

    /**
     * Render a cObject
     *
     * @param string $name The name of the cobject (COA|TEXT)
     * @param array $conf The configuration array
     * @return string The rendered cObject
     */
    private function getCobject($name, $conf)
    {
        if (is_null($this->cObjRenderer)) {
            $this->cObjRenderer = new ContentObjectRenderer();
        }
        return $this->cObjRenderer->cObjGetSingle($name, $conf);
    }

    /**
     * Transform the values before inserting as javascript
     *
     * @param string $value The value
     * @param string $type The type of transformation
     * @return mixed The transformed value
     */
    private function prepareValue($value, $type)
    {
        switch ($type) {
            case 'int':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'boolean':
                $value = ($value ? 'true' : 'false');
                break;
            case 'object':
                break;
            default:
                $value = '\'' . $value . '\'';
                break;
        }
        return $value;
    }

}
