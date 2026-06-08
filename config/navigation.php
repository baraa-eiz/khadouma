<?php

/**
 * navigation.php
 * Configuration-driven sidebar menu registry for the Admin panel.
 */

return [
    [
        'key' => 'dashboard',
        'label' => 'لوحة التحكم',
        'icon' => 'home',
        'url' => '/admin/dashboard',
        'active_pattern' => '/admin/dashboard'
    ],
    [
        'key' => 'services',
        'label' => 'الخدمات',
        'icon' => 'briefcase',
        'url' => '/admin/services',
        'active_pattern' => '/admin/services*'
    ],
    [
        'key' => 'cities',
        'label' => 'المدن',
        'icon' => 'globe',
        'url' => '#',
        'active_pattern' => '/admin/cities*'
    ],
    [
        'key' => 'areas',
        'label' => 'المناطق',
        'icon' => 'map-pin',
        'url' => '#',
        'active_pattern' => '/admin/areas*'
    ],
    [
        'key' => 'providers',
        'label' => 'مزودو الخدمات',
        'icon' => 'users',
        'url' => '#',
        'active_pattern' => '/admin/providers*'
    ],
    [
        'key' => 'reviews',
        'label' => 'التقييمات',
        'icon' => 'star',
        'url' => '#',
        'active_pattern' => '/admin/reviews*'
    ],
    [
        'key' => 'reports',
        'label' => 'الشكاوى والتقارير',
        'icon' => 'alert-triangle',
        'url' => '#',
        'active_pattern' => '/admin/reports*'
    ],
    [
        'key' => 'settings',
        'label' => 'الإعدادات العامة',
        'icon' => 'settings',
        'url' => '#',
        'active_pattern' => '/admin/settings*'
    ]
];
