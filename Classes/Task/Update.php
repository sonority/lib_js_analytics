<?php

namespace Sonority\LibJsAnalytics\Task;

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

/**
 * Provides google-analytics script-downloader
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libjsanalytics
 */
class Update extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    /**
     * This is the main method that is called when a task is executed
     *
     * @return bool Returns true on successful execution, false on error
     */
    public function execute()
    {
        $report = Tools::updateJavaScript();
        if ($report['error']) {
            return false;
        } else {
            return true;
        }
    }

}
