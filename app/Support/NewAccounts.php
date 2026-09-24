<?php

namespace App\Support;

use App\Core\Session;

/**
 * Temporary passwords of accounts an admin has just created, shown to that
 * admin once on the next page (resources/views/partials/new-accounts.php).
 * The welcome email also carries them, but email may not arrive, and without
 * this the admin had no way to hand the details over.
 */
class NewAccounts
{
    /** Portal keys → label and sign-in address. */
    public const PORTALS = [
        'student' => ['Student Portal', '/student-portal'],
        'lecturer' => ['Lecturer Portal', '/lecturer-portal'],
        'corporate' => ['Corporate Portal', '/login'],
        'staff' => ['Admin Portal', '/login?portal=staff'],
    ];

    public static function add(string $name, string $email, string $password, string $portal, bool $emailed): void
    {
        $accounts = Session::getFlash('new_accounts', []);
        $accounts[] = compact('name', 'email', 'password', 'portal', 'emailed');
        Session::flash('new_accounts', $accounts);
    }

    /** The portal a newly created account with this role signs in to. */
    public static function portalForRole(string $role): string
    {
        return match ($role) {
            'learner' => 'student',
            'lecturer' => 'lecturer',
            'corporate_contact' => 'corporate',
            default => 'staff',
        };
    }

    public static function loginUrl(string $portal): string
    {
        return url(self::PORTALS[$portal][1] ?? '/login');
    }
}
