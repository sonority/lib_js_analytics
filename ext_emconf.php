<?php

/* * *************************************************************
 * Extension Manager/Repository config file for ext "lib_js_analytics".
 *
 * Auto generated 01-02-2016 21:44
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 * ************************************************************* */

$EM_CONF[$_EXTKEY] = [
    'title' => 'JS Library: Google Analytics',
    'description' => 'This extension simple integrates google\'s analytics.js into your website. Configurable with constants-editor and equipped with a scheduler task to periodically download a local copy of analytics.js if you don\'t want to use Google\'s CDN.',
    'category' => 'fe',
    'version' => '0.1.1',
    'state' => 'beta',
    'uploadfolder' => true,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'stephan.kellermayr@gmail.com',
    'author_company' => 'sonority.at - MULTIMEDIA ART DESIGN',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-7.6.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
