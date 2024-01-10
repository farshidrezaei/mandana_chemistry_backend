<?php

return [
    'user' => [
        'title' => "کاربر",
        'plural' => "کاربرها",
        'group' => '',
    ],
    'test' => [
        'title' => "آزمایش",
        'plural' => "آزمایش‌ها",
        'group' => '',
    ],
    'product' => [
        'title' => "محصول",
        'plural' => "محصول‌ها",
        'group' => '',
    ],
    'project' => [
        'title' => "پروژه",
        'plural' => "پروژه‌ها",
        'group' => '',
        'filters'=>[
            'tabs'=>[
                'all'=>'همه',
                'testing'=>'درحال آزمایش',
                'done'=>'آماده تحویل',
                'failed'=>'عدم تطابق',
                'archived'=>'آرشیو شده',
            ]
        ]
    ],

];
