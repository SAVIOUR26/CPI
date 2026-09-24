<?php

namespace App\Support;

/**
 * CPI's public contact details, coverage and training modes, as supplied by
 * the client. The top bar, phone menu, footer, Contact and About pages, the
 * WhatsApp button and the search-engine details all read from here.
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

    public static function tel(string $number): string
    {
        return 'tel:' . preg_replace('/[^0-9+]/', '', $number);
    }

    public static function whatsappUrl(?string $message = null): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', self::WHATSAPP) . '?text=' . rawurlencode($message ?? self::WHATSAPP_GREETING);
    }
}
