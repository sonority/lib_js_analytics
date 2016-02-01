<?php

namespace Sonority\LibJsAnalytics;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Provides google-analytics script-downloader
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
abstract class Tools
{

    protected static $configuration = null;
    protected static $registry = null;
    protected static $extensionKey = 'lib_js_analytics';
    protected static $sourceFile = '';
    protected static $localFile = '';
    protected static $report = [];
    protected static $code = '';

    public function __construct()
    {

    }

    /**
     * Download and update the Google-Analytics-Javascript (analytics.js)
     *
     * @return mixed
     */
    public static function updateJavaScript()
    {
        if (self::checkLocalFile() && self::getContentFromSourceFile()) {
            self::writeJavascriptFile();
        }
        return self::$report;
    }

    /**
     * Check if the local file is writable
     *
     * @return bool true if the file is writeable
     */
    private static function checkLocalFile()
    {
        self::$localFile = self::getConfParam('localFile');
        self::$localFile = GeneralUtility::getFileAbsFileName(self::$localFile);
        if (empty(self::$localFile)) {
            self::setReport(true, 'checkLocalFile.targetError', [self::$localFile]);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Download the content of the sourceFile
     *
     * @return bool true if the content could be downloaded successfully.
     */
    private static function getContentFromSourceFile()
    {
        $success = false;
        self::$sourceFile = self::getConfParam('sourceFile');
        if (substr(self::$sourceFile, 0, 4) !== 'http') {
            self::$sourceFile = (GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https:' : 'http:') . self::$sourceFile;
        }
        // Get file content
        self::$code = GeneralUtility::getURL(self::$sourceFile, 1, false, self::$report);
        if (self::$report['error'] === 0) {
            if (self::$report['http_code'] === '404') {
                self::setReport(true, 'getContentFromSourceFile.404', [self::linkSourceUrl()]);
            } elseif (strpos(self::$report['content_type'], 'text/javascript') !== 0) {
                self::setReport(true, 'getContentFromSourceFile.wrongType', [self::linkSourceUrl()]);
            } elseif (empty(self::$code)) {
                self::setReport(true, 'getContentFromSourceFile.emptyFile', [self::linkSourceUrl()]);
            } else {
                $success = true;
            }
        } else {
            self::setReport(true, 'getContentFromSourceFile.error', [self::linkSourceUrl(),
                self::$report['message']]);
        }
        return $success;
    }

    /**
     * Write the downloaded javascript-code into local file
     *
     * @return bool true if the file was successfully opened and written to.
     */
    private static function writeJavascriptFile()
    {
        self::stripHttpHeaders();
        $fileIsOk = GeneralUtility::writeFile(self::$localFile, self::$code);
        if ($fileIsOk) {
            self::setReport(false, 'writeJavascriptFile.success', [self::linkSourceUrl(),
                self::$localFile]);
        } else {
            self::setReport(true, 'writeJavascriptFile.writeError', [self::$localFile]);
        }
    }

    /**
     *
     */
    private static function setReport($error, $languageKey, $replacement = [])
    {
        $message = LocalizationUtility::translate('tools.' . $languageKey, self::$extensionKey, $replacement);
        self::$report['error'] = $error;
        self::$report['message'] = $message;
        if ($error) {
            Tools::getLogger(__CLASS__)->error($message);
        }
    }

    /**
     * Strips HTTP headers from the content.
     *
     * @return void
     */
    protected static function stripHttpHeaders()
    {
        $headersEndPos = strpos(self::$code, "\r\n\r\n");
        if ($headersEndPos) {
            self::$code = substr(self::$code, $headersEndPos + 4);
        }
    }

    /**
     * Wrap the sourceUrl
     *
     * @return string
     */
    private static function linkSourceUrl()
    {
        return '<a href="' . self::$sourceFile . '" target="_blank" title="Google Analytics">' . self::$sourceFile . '</a>';
    }

    /**
     * Get a configuration parameter
     *
     * @param string $key Parameter key
     * @return mixed Parameter value
     */
    public static function getConfParam($key)
    {
        if (!is_array(self::$configuration)) {
            self::$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lib_js_analytics']);
        }
        return self::$configuration[$key];
    }

    /**
     * Returns a logger for given class
     *
     * @param string $class
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger($class)
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger($class);
    }

}
