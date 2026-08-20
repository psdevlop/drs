<?php

namespace App\Support;

final class NepaliGovernmentHolidays
{
    public const YEAR_BS = 2083;
    public const SOURCE_URL = 'https://nepalipatro.com.np/en/government-holiday';

    public static function dates(): array
    {
        return [
            ['date' => '2026-04-14', 'reason' => 'Nepali New Year Start'],
            ['date' => '2026-04-19', 'reason' => 'Public Holiday'],
            ['date' => '2026-04-26', 'reason' => 'Public Holiday'],
            ['date' => '2026-05-01', 'reason' => 'Buddha Jayanti; International Labour Day; Ubhauli Parba'],
            ['date' => '2026-05-03', 'reason' => 'Public Holiday'],
            ['date' => '2026-05-10', 'reason' => 'Public Holiday'],
            ['date' => '2026-05-17', 'reason' => 'Public Holiday'],
            ['date' => '2026-05-24', 'reason' => 'Public Holiday'],
            ['date' => '2026-05-28', 'reason' => 'Bakar Eid'],
            ['date' => '2026-05-29', 'reason' => 'Republic Day'],
            ['date' => '2026-05-31', 'reason' => 'Public Holiday'],
            ['date' => '2026-06-07', 'reason' => 'Public Holiday'],
            ['date' => '2026-06-14', 'reason' => 'Public Holiday'],
            ['date' => '2026-06-21', 'reason' => 'Public Holiday'],
            ['date' => '2026-06-28', 'reason' => 'Public Holiday'],
            ['date' => '2026-07-05', 'reason' => 'Public Holiday'],
            ['date' => '2026-07-12', 'reason' => 'Public Holiday'],
            ['date' => '2026-07-19', 'reason' => 'Public Holiday'],
            ['date' => '2026-07-26', 'reason' => 'Public Holiday'],
            ['date' => '2026-08-02', 'reason' => 'Public Holiday'],
            ['date' => '2026-08-09', 'reason' => 'Public Holiday'],
            ['date' => '2026-08-16', 'reason' => 'Public Holiday'],
            ['date' => '2026-08-28', 'reason' => 'Raksha Bandhan'],
            ['date' => '2026-08-29', 'reason' => 'Public Holiday'],
            ['date' => '2026-08-30', 'reason' => 'Shree Krishna Janmasthami Brata (Moharatri)'],
            ['date' => '2026-09-04', 'reason' => 'Shree Krishna Janmasthami Brata (Moharatri); Gaura Parwa (Related Field only)'],
            ['date' => '2026-09-06', 'reason' => 'Public Holiday'],
            ['date' => '2026-09-13', 'reason' => 'Public Holiday'],
            ['date' => '2026-09-14', 'reason' => 'Haritalika Teej Brata (Women only)'],
            ['date' => '2026-09-19', 'reason' => 'Constitution Day'],
            ['date' => '2026-09-20', 'reason' => 'Public Holiday'],
            ['date' => '2026-09-27', 'reason' => 'Public Holiday'],
            ['date' => '2026-10-04', 'reason' => 'Jiwatputrika Brat/Jitiya Parwa (Womens celebrating Jitiya Parwa only); Public Holiday; Ghatasthapana (Na:laa Swone)'],
            ['date' => '2026-10-11', 'reason' => 'Public Holiday; Nawa Patrika Prawes (Fulpati)'],
            ['date' => '2026-10-17', 'reason' => 'Mahaastami Barta'],
            ['date' => '2026-10-18', 'reason' => 'Public Holiday; Dashain Holiday'],
            ['date' => '2026-10-19', 'reason' => 'Dashain Holiday'],
            ['date' => '2026-10-20', 'reason' => 'Mahanawami'],
            ['date' => '2026-10-21', 'reason' => 'Vijaya Dashami (Dashainko Tika)'],
            ['date' => '2026-10-22', 'reason' => 'Dashain Holiday'],
            ['date' => '2026-10-23', 'reason' => 'Dashain Holiday'],
            ['date' => '2026-10-25', 'reason' => 'Public Holiday'],
            ['date' => '2026-11-01', 'reason' => 'Dipawali (Laxmi Puja)'],
            ['date' => '2026-11-08', 'reason' => 'Public Holiday; Gai Tihar-Puja'],
            ['date' => '2026-11-09', 'reason' => 'Gai Tihar-Puja'],
            ['date' => '2026-11-10', 'reason' => 'Goru Tihar Puja'],
            ['date' => '2026-11-11', 'reason' => 'Falgunanda Jayanti (Kirat only); Bhai Tika (Kija Puja)'],
            ['date' => '2026-11-12', 'reason' => 'Tihar Holiday'],
            ['date' => '2026-11-15', 'reason' => 'Public Holiday'],
            ['date' => '2026-11-22', 'reason' => 'Guru Nanak Jayanti (Sikh only)'],
            ['date' => '2026-11-24', 'reason' => 'Guru Nanak Jayanti (Sikh only)'],
            ['date' => '2026-11-29', 'reason' => 'International Day of Persons with Disabilities (Differently abled only)'],
            ['date' => '2026-12-03', 'reason' => 'International Day of Persons with Disabilities (Differently abled only)'],
            ['date' => '2026-12-06', 'reason' => 'Public Holiday'],
            ['date' => '2026-12-13', 'reason' => 'Public Holiday'],
            ['date' => '2026-12-20', 'reason' => 'Udhauli Parwa'],
            ['date' => '2026-12-24', 'reason' => 'Udhauli Parwa; Yo:mari Punhi'],
            ['date' => '2026-12-25', 'reason' => 'Christmas (Christian only)'],
            ['date' => '2026-12-27', 'reason' => 'Tamu (Gurung) Lhosar'],
            ['date' => '2026-12-30', 'reason' => 'Tamu (Gurung) Lhosar'],
            ['date' => '2027-01-03', 'reason' => 'Public Holiday'],
            ['date' => '2027-01-10', 'reason' => 'Prithivi Jayanti, National Unity Day'],
            ['date' => '2027-01-11', 'reason' => 'Prithivi Jayanti, National Unity Day'],
            ['date' => '2027-01-15', 'reason' => 'Maghi Parwa(Tharu, Magar, chhantyaalaadiko parba); Maghe Sankranti'],
            ['date' => '2027-01-17', 'reason' => 'Public Holiday'],
            ['date' => '2027-01-24', 'reason' => 'Constitution Day'],
            ['date' => '2027-01-30', 'reason' => "Martyrs' Day; Public Holiday"],
            ['date' => '2027-01-31', 'reason' => 'Sonam (Tamang) Lhosar'],
            ['date' => '2027-02-07', 'reason' => 'Public Holiday; Shree Panchami(Basanta Shrawan) (For Education Institute only)'],
            ['date' => '2027-02-11', 'reason' => 'Shree Panchami(Basanta Shrawan) (For Education Institute only)'],
            ['date' => '2027-02-14', 'reason' => 'National Democracy Day'],
            ['date' => '2027-02-19', 'reason' => 'National Democracy Day'],
            ['date' => '2027-02-21', 'reason' => 'Public Holiday'],
            ['date' => '2027-02-28', 'reason' => 'Maha Shivaratri Brat'],
            ['date' => '2027-03-06', 'reason' => 'Public Holiday'],
            ['date' => '2027-03-07', 'reason' => "International Women's Day"],
            ['date' => '2027-03-08', 'reason' => "International Women's Day"],
            ['date' => '2027-03-09', 'reason' => 'Gyalpo Lhosar'],
            ['date' => '2027-03-14', 'reason' => 'Public Holiday'],
            ['date' => '2027-03-21', 'reason' => 'Pahadma Holi; Public Holiday'],
            ['date' => '2027-03-22', 'reason' => 'Terai Holi'],
            ['date' => '2027-03-28', 'reason' => 'Public Holiday'],
            ['date' => '2027-04-04', 'reason' => 'Public Holiday'],
            ['date' => '2027-04-06', 'reason' => 'Ghode Jatra (KTM valley only)'],
            ['date' => '2027-04-11', 'reason' => 'Public Holiday'],
        ];
    }

    public static function datesJson(): string
    {
        return json_encode(self::dates(), JSON_UNESCAPED_SLASHES);
    }

    public static function forSchedule(array $holidays): array
    {
        return array_values(array_filter(array_map(function (array $entry) {
            $reason = trim($entry['reason'] ?? '');

            if (strcasecmp($reason, 'Public Holiday') === 0) {
                return null;
            }

            if ($reason !== '') {
                $parts = array_values(array_filter(array_map('trim', explode(';', $reason)), function (string $part) {
                    return strcasecmp($part, 'Public Holiday') !== 0;
                }));

                $entry['reason'] = implode('; ', $parts);
            }

            return $entry;
        }, $holidays)));
    }
}
