<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Country name to code mapping.
     */
    protected const COUNTRY_CODES = [
        'Afghanistan' => 'AF',
        'Albania' => 'AL',
        'Algeria' => 'DZ',
        'Argentina' => 'AR',
        'Australia' => 'AU',
        'Austria' => 'AT',
        'Belgium' => 'BE',
        'Brazil' => 'BR',
        'Canada' => 'CA',
        'Chile' => 'CL',
        'China' => 'CN',
        'Colombia' => 'CO',
        'Croatia' => 'HR',
        'Czech Republic' => 'CZ',
        'Czechia' => 'CZ',
        'Denmark' => 'DK',
        'Egypt' => 'EG',
        'Finland' => 'FI',
        'France' => 'FR',
        'Germany' => 'DE',
        'Greece' => 'GR',
        'Hong Kong' => 'HK',
        'Hungary' => 'HU',
        'India' => 'IN',
        'Indonesia' => 'ID',
        'Ireland' => 'IE',
        'Israel' => 'IL',
        'Italy' => 'IT',
        'Japan' => 'JP',
        'Malaysia' => 'MY',
        'Mexico' => 'MX',
        'Netherlands' => 'NL',
        'The Netherlands' => 'NL',
        'New Zealand' => 'NZ',
        'Norway' => 'NO',
        'Pakistan' => 'PK',
        'Philippines' => 'PH',
        'Poland' => 'PL',
        'Portugal' => 'PT',
        'Romania' => 'RO',
        'Russia' => 'RU',
        'Saudi Arabia' => 'SA',
        'Singapore' => 'SG',
        'South Africa' => 'ZA',
        'South Korea' => 'KR',
        'Spain' => 'ES',
        'Sweden' => 'SE',
        'Switzerland' => 'CH',
        'Taiwan' => 'TW',
        'Thailand' => 'TH',
        'Turkey' => 'TR',
        'Ukraine' => 'UA',
        'United Arab Emirates' => 'AE',
        'United Kingdom' => 'GB',
        'United States' => 'US',
        'Vietnam' => 'VN',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::COUNTRY_CODES as $name => $code) {
            DB::table('page_views')
                ->where('country', $name)
                ->update(['country' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $reversed = array_flip(self::COUNTRY_CODES);

        foreach ($reversed as $code => $name) {
            DB::table('page_views')
                ->where('country', $code)
                ->update(['country' => $name]);
        }
    }
};
