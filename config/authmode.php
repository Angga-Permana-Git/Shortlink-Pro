<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Mode
    |--------------------------------------------------------------------------
    |
    | Controls how users authenticate:
    | - local: standard email/password against the users table.
    | - ldap: credentials validated against an LDAP directory.
    | - sso: single sign-on via an identity provider (subject match).
    |
    */

    'mode' => env('AUTH_MODE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Configuration
    |--------------------------------------------------------------------------
    */
    'ldap' => [
        'host' => env('LDAP_HOST'),
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN'),
        'username_dn' => env('LDAP_USERNAME_DN', 'uid={username},{base_dn}'),
        'username_attribute' => env('LDAP_USERNAME_ATTRIBUTE', 'uid'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    */
    'sso' => [
        'identifier_attribute' => env('SSO_IDENTIFIER_ATTRIBUTE', 'sub'),
    ],
];