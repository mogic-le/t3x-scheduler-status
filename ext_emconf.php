<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Scheduler Monitoring',
    'description' => '',
    'category' => 'plugin',
    'author' => '',
    'author_email' => '',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'scheduler' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
