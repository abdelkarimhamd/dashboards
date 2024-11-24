<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'tendering_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\TenderingUser::class,
    ],
        'hr_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\HR\User::class,
    ],
        'crm_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\CRM\User::class,
    ],
        'procurement_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\ProcurementModels\ProcurementUser::class,
    ],
    
    'plusers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Pluser::class,
    ],
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

    'tendering_users' => [
        'provider' => 'tendering_users', // Must match the provider name defined above
        'table' => 'password_resets',    // Use the default password_resets table
        'expire' => 60,                   // Token expiration time in minutes
        'throttle' => 60,                 // Throttle time in minutes
    ],

    'crm_users' => [
        'provider' => 'crm_users', // Must match the provider name defined above
        'table' => 'password_resets',    // Use the default password_resets table
        'expire' => 60,                   // Token expiration time in minutes
        'throttle' => 60,                 // Throttle time in minutes
    ],

    'hr_users' => [
        'provider' => 'hr_users', // Must match the provider name defined above
        'table' => 'password_resets',    // Use the default password_resets table
        'expire' => 60,                   // Token expiration time in minutes
        'throttle' => 60,                 // Throttle time in minutes
    ],
    'procurement_users' => [
        'provider' => 'procurement_users',
        'table' => 'password_resets', // or a specific table for this user type if needed
        'expire' => 60, // Link expiry time in minutes
        'throttle' => 60, // Minimum time (in seconds) between reset attempts
    ],
    'plusers' => [
        'provider' => 'plusers',
        'table' => 'password_resets', // or a specific table for this user type if needed
        'expire' => 60, // Link expiry time in minutes
        'throttle' => 60, // Minimum time (in seconds) between reset attempts
    ],
    'users' => [
        'provider' => 'users',
        'table' => 'password_resets', // or a specific table for this user type if needed
        'expire' => 60, // Link expiry time in minutes
        'throttle' => 60, // Minimum time (in seconds) between reset attempts
    ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

];
