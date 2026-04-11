<?php

namespace ArRahmouni\ResponseHelper;

use Illuminate\Http\Request;

class RequestAdminArea
{
    /**
     * Whether the request targets the admin control panel route prefix ({locale?}/admin/...).
     */
    public static function isAdminControlPanel(Request $request): bool
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        $segments = $request->segments();
        $first = $segments[0] ?? '';

        if (in_array($first, $locales, true)) {
            return ($segments[1] ?? '') === 'admin';
        }

        return $first === 'admin';
    }
}
