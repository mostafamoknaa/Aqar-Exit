<?php

/**
 * Permission matrix defaults. Admins can edit the DB-backed matrix at
 * Admin → Permissions; these defaults apply until a permission is stored
 * in the database (and keep factories/tests working without seeding).
 */
return [
    'catalog' => [
        'dashboard.view' => ['label' => 'View dashboard', 'roles' => ['admin', 'manager']],
        'listings.view' => ['label' => 'View listings', 'roles' => ['admin', 'manager']],
        'listings.create' => ['label' => 'Create/edit listings', 'roles' => ['admin', 'manager']],
        'listings.moderate' => ['label' => 'Moderate listings (publish/reject/request changes)', 'roles' => ['admin', 'manager']],
        'listings.delete' => ['label' => 'Delete listings', 'roles' => ['admin']],
        'documents.review' => ['label' => 'Review/download private documents', 'roles' => ['admin', 'manager']],
        'financials.verify' => ['label' => 'Verify financial figures', 'roles' => ['admin', 'manager']],
        'deals.view' => ['label' => 'View assignment cases', 'roles' => ['admin', 'manager']],
        'deals.transition' => ['label' => 'Move case stages (assigned manager or admin)', 'roles' => ['admin', 'manager']],
        'interests.manage' => ['label' => 'Manage inquiries + reply', 'roles' => ['admin', 'manager']],
        'evaluations.manage' => ['label' => 'Manage exit evaluations', 'roles' => ['admin', 'manager']],
        'users.view' => ['label' => 'View users', 'roles' => ['admin', 'manager']],
        'users.manage' => ['label' => 'Manage users/roles/suspension', 'roles' => ['admin']],
        'catalog.manage' => ['label' => 'Manage developers/projects/categories/locations', 'roles' => ['admin', 'manager']],
        'reports.view' => ['label' => 'View reports', 'roles' => ['admin', 'manager']],
        'reports.export' => ['label' => 'Export reports/listings CSV', 'roles' => ['admin']],
        'settings.view' => ['label' => 'View settings', 'roles' => ['admin', 'manager']],
        'settings.manage' => ['label' => 'Change settings (name/logo/commission)', 'roles' => ['admin']],
        'permissions.manage' => ['label' => 'Edit permission matrix', 'roles' => ['admin']],
        'audit.view' => ['label' => 'View audit logs', 'roles' => ['admin']],
    ],
];
