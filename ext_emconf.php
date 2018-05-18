<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Tellmatic',
    'description' => 'Provides methods to communicate with a tellmatic server.',
    'category' => 'plugin',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Alexander Stehlik',
    'author_email' => 'alexander.stehlik.deleteme@googlemail.com',
    'author_company' => '',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-0.0.0',
            'typo3' => '6.2.3-7.99.99',
        ],
        'conflicts' => [],
        'suggests' => ['formhandler_subscription' => '0.0.0-0.0.0'],
    ],
];
