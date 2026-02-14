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
            'title'             => 'نجاح',
            'description'       => 'تمت العملية بنجاح',
        ],
        'error'                 => [
            'title'             => 'خطأ !!',
            'description'       => 'لقد حدث خطأ ما !!',
        ],
    ],

    'web_response_messages'     => [
        'validation_error'      => 'الرجاء التحقق من البيانات التي قمت بإدخالها والمحاولة مرة أخرى.',
        'under_maintenance'     => 'التطبيق تحت الصيانة.',
        'dont_have_permission'  => 'ليس لديك الصلاحيات الكافية لتنفيذ هذا الإجراء.',
        'record_not_found'      => 'السجل غير موجود',
        'method_not_allowed'    => 'نأسف، لكن طريقة الطلب التي استخدمتها غير مسموحة لهذا الرابط.',
        'login_required'        => 'الرجاء تسجيل الدخول للوصول إلى هذه الصفحة.',
        'too_many_requests'     => 'لقد تجاوزت الحد المسموح به للمحاولات المتكررة. يرجى المحاولة مرة أخرى لاحقًا.',
    ],
    'api_response_messages'         => [
        'data_loaded_successfully'  => 'تم جلب البيانات بنجاح.',
    ],
];

