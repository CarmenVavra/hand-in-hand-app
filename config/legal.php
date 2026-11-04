<?php

/*
| Angaben für Impressum und Datenschutzerklärung.
| Vor dem Livegang in der .env befüllen (LEGAL_*).
*/
return [
    'name' => env('LEGAL_NAME'),
    'street' => env('LEGAL_STREET'),
    'city' => env('LEGAL_CITY'),
    'country' => env('LEGAL_COUNTRY', 'Österreich'),
    'email' => env('LEGAL_EMAIL'),
    'phone' => env('LEGAL_PHONE'),
    'vat_id' => env('LEGAL_VAT_ID'),
];
