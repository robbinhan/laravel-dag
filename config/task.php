<?php
return [
    'flow1' => [
        'deps' =>
            [
                'task1' => [
                    'class' => Task1::class,
                    'deps' => [
                        'task2' => [
                            'class' => Task2::class,
                        ],
                    ],
                ],
                'task3' => [
                    'class' => Task3::class,
                ]
            ]
    ],
    'flow2' => [
        'deps' =>
            [
                'task3' => [
                    'class' => Task3::class
                ]
            ]
    ]
];