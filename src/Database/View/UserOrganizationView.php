<?php

namespace Exceedone\Exment\Database\View;

use Exceedone\Exment\Enums\SystemTableName;

class UserOrganizationView
{
    /**
     * create user organization view sql
     */
    public static function createUserOrganizationView()
    {
        $subquery = getModelName(SystemTableName::USER)
            ::select([
                \DB::raw("1 as type_no"),
                \DB::raw("'user' as type"),
                'id',
                'suuid',
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.user_code')) as code"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.user_name')) as name"),
                \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.email')) as email"),
            ]);

        $subquery2 = getModelName(SystemTableName::ORGANIZATION)
        ::select([
            \DB::raw("2 as type_no"),
            \DB::raw("'organization' as type"),
            'id',
            'suuid',
            \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.organization_code')) as code"),
            \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.organization_name')) as name"),
            \DB::raw("null as email"),
        ]);

        $subquery->union($subquery2);
        
        return $subquery;
    }
}
