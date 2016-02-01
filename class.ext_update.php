<?php

/**
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update class for the extension manager.
 *
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
class ext_update
{

    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = [];

    /**
     * Main update function called by the extension manager
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry should by displayed
     *
     * @return bool
     * @todo find a better way to determine if update is needed or not.
     */
    public function access()
    {
        return true;
    }

    /**
     * The actual update function. Add your update task in here
     *
     * @return void
     */
    protected function processUpdates()
    {
        $report = Tools::updateJavaScript();
        if ($report['error']) {
            $this->messageArray[] = [FlashMessage::ERROR, 'ERROR', $report['message']];
        } else {
            $this->messageArray[] = [FlashMessage::OK, 'OK', $report['message']];
        }
    }

    /**
     * Generate output by using flash-messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        $output = '';
        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                    'TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $messageItem[2], $messageItem[1], $messageItem[0]);
            $output .= $flashMessage->render();
        }
        return $output;
    }

}
