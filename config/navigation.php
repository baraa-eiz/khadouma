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
        'url' => '/admin/cities',
        'active_pattern' => '/admin/cities*'
    ],
    [
        'key' => 'areas',
        'label' => 'المناطق',
        'icon' => 'map-pin',
        'url' => '/admin/areas',
        'active_pattern' => '/admin/areas*'
    ],
    [
        'key' => 'providers',
        'label' => 'مزودو الخدمات',
        'icon' => 'users',
        'url' => '/admin/providers',
        'active_pattern' => '/admin/providers*'
    ],
    [
        'key' => 'users',
        'label' => 'المستخدمين العامة',
        'icon' => 'users',
        'url' => '/admin/users',
        'active_pattern' => '/admin/users*'
    ],
    [
        'key' => 'drafts',
        'label' => 'مراجعة طلبات التعديل',
        'icon' => 'alert-triangle',
        'url' => '/admin/providers/drafts',
        'active_pattern' => '/admin/providers/drafts*'
    ],
    [
        'key' => 'verification',
        'label' => 'توثيق الحسابات',
        'icon' => 'shield',
        'url' => '/admin/verification',
        'active_pattern' => '/admin/verification*'
    ],
    [
        'key' => 'productivity',
        'label' => 'أدوات الإنتاجية',
        'icon' => 'zap',
        'url' => '/admin/productivity/quality',
        'active_pattern' => '/admin/productivity*'
    ],
    [
        'key' => 'reviews',
        'label' => 'التقييمات',
        'icon' => 'star',
        'url' => '/admin/reviews',
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
