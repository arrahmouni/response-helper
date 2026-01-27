<?php

/*
|--------------------------------------------------------------------------
| Package Translations
|--------------------------------------------------------------------------
|
| Default translation namespace for the package: `response`.
| Default translation file: `messages`.
|
*/

return [
    'response_message_types'    => [
        'success'               => [
            'title'             => 'Success',
            'description'       => 'Operation done successfully',
        ],
        'error'                 => [
            'title'             => 'Error !!',
            'description'       => 'Something went wrong !!',
        ],
    ],
    'web_response_messages'     => [
        'validation_error'      => 'Please check the data you entered and try again.',
        'under_maintenance'     => 'Under maintenance.',
        'dont_have_permission'  => 'You do not have sufficient permissions to perform this action.',
        'record_not_found'      => 'Record not found',
        'method_not_allowed'    => 'We\'re sorry, but the request method you used is not allowed for this URL.',
        'login_required'        => 'You need to login to access this page.',
    ],
    'api_response_messages'         => [
        'data_loaded_successfully'  => 'Data loaded successfully.',
    ],
];

