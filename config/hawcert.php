<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HawCert - Autenticación por certificado
    |--------------------------------------------------------------------------
    | Base URL de tu instalación HawCert y slug del servicio para esta app.
    */

    'base_url' => rtrim(env('HAWCERT_BASE_URL', 'https://hawcert.hawkins.es'), '/'),

    'service_slug' => env('HAWCERT_SERVICE_SLUG', 'alquiler-piso'),

];
