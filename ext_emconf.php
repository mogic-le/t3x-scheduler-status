<?php
$EM_CONF[$_EXTKEY] = [
    'title'        => 'Scheduler status',
    'description'  => 'API to monitor scheduler task execution status',
    'category'     => 'plugin',
    'author'       => 'Christian Weiske, Mogic GmbH',
    'author_email' => 'weiske@mogic.com',
    'state'        => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'scheduler' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
