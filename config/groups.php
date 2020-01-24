<?php
// Groups plugin configuration
return [
    'Groups' => [
        'defaultGroup' => 'Everyone',
        'systemGroups' => [
            [
                'name' => 'Admins',
                'description' => 'Administrators of the system',
                'deny_edit' => false,
                'deny_delete' => true,
            ],
            [
                'name' => 'Everyone',
                'description' => 'All users',
                'deny_edit' => true,
                'deny_delete' => true,
            ],
        ],
        'remoteGroups' => [
            'enabled' => false,
            'LDAP' => [],
        ],
    ],
];
