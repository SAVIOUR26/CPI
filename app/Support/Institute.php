<?php

namespace App\Support;

/**
 * CPI's public contact details, coverage, training modes and Mobile Money
 * payment details, as supplied by the client. The top bar, phone menu,
 * footer, Contact and About pages, the WhatsApp button, the search-engine
 * details and the payment pages all read from here.
 */
final class Institute
{
    public const LOCATION = 'Kampala, Uganda';
    public const COVERAGE = 'Uganda & across Africa';
    public const EMAIL = 'info@crawfordinstitute.online';
    /** Main line first. */
    public const PHONES = ['+256 393 194884', '+256 740 245057'];
    public const WHATSAPP = '+256 740 245057';
    public const WHATSAPP_GREETING = 'Hello CPI, I would like to know more about your training programmes.';

    /**
     * How students pay (the client's instruction, Sep 2026): Mobile Money to one of these numbers, registered to
     * CPI's Principal Accountant; the student uploads the screenshot in the Student Portal and Finance approves it.
     * [network, number]
     */
    public const MOBILE_MONEY = [
        ['Airtel Money', '0740 245057'],
        ['MTN Mobile Money', '0773 539831'],
    ];
    public const MOBILE_MONEY_NAME = 'JAMES OPIO';
    public const MOBILE_MONEY_ROLE = 'Principal Accountant';

    /** [icon, name, short description] */
    public const TRAINING_MODES = [
        ['fa-laptop', 'Online Training', 'Learn remotely, from anywhere in Uganda or across Africa.'],
        ['fa-building', 'On-site Corporate Training', 'We deliver the training at your organization\'s premises.'],
        ['fa-people-group', 'Customized In-House Training', 'Programmes designed around your team\'s specific needs.'],
        ['fa-chalkboard-user', 'Physical Training at Designated Training Locations', 'Face-to-face sessions at our designated training venues.'],
    ];

    /** Delivery modes stored on corporate requests and intakes: [label, short label]. */
    public const MODES = [
        'onsite' => ['On-site corporate training (at your premises)', 'On-site'],
        'in_house' => ['Customized in-house training', 'In-house'],
        'online' => ['Online training', 'Online'],
        'in_person' => ['Physical training at a designated training location', 'Physical (training venue)'],
        'hybrid' => ['Hybrid (online and physical)', 'Hybrid'],
    ];

    public static function modeLabel(?string $mode, bool $short = false): string
    {
        return self::MODES[$mode][$short ? 1 : 0] ?? ucfirst(str_replace('_', ' ', (string) $mode));
    }

    /** "0740 245057 (Airtel Money) or 0773 539831 (MTN Mobile Money), registered to JAMES OPIO, CPI's Principal Accountant" */
    public static function mobileMoneySummary(): string
    {
        return implode(' or ', array_map(fn ($m) => $m[1] . ' (' . $m[0] . ')', self::MOBILE_MONEY))
            . ', registered to ' . self::MOBILE_MONEY_NAME . ', CPI\'s ' . self::MOBILE_MONEY_ROLE;
    }

    public static function tel(string $number): string
    {
        return 'tel:' . preg_replace('/[^0-9+]/', '', $number);
    }

    public static function whatsappUrl(?string $message = null): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', self::WHATSAPP) . '?text=' . rawurlencode($message ?? self::WHATSAPP_GREETING);
    }
}
