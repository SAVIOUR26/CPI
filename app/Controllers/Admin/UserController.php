<?php

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->requirePermission('users.manage');
        $search = $request->input('q');
        $sql = 'SELECT u.*, GROUP_CONCAT(r.slug SEPARATOR ", ") AS role_slugs
                FROM users u LEFT JOIN role_user ru ON ru.user_id = u.id LEFT JOIN roles r ON r.id = ru.role_id';
        $params = [];
        if ($search) {
            $sql .= ' WHERE u.full_name LIKE ? OR u.email LIKE ?';
            $params = ["%$search%", "%$search%"];
        }
        $sql .= ' GROUP BY u.id ORDER BY u.created_at DESC';
        $users = User::query($sql, $params);
        $roles = Role::all('name');

        $this->view('admin.users.index', [
            'pageTitle' => 'Users & Roles — CPI Admin',
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
        ], 'layouts.dashboard');
    }

    public function create(Request $request): void
    {
        $this->requirePermission('users.manage');
        $this->verifyCsrf($request);

        $data = $this->validate($request, [
            'full_name' => 'required|max:150',
            'email' => 'required|email',
        ]);

        if (User::findByEmail($data['email'])) {
            $this->flash('error', 'A user with that email already exists.');
            $this->redirect('/admin/users');
            return;
        }

        $tempPassword = bin2hex(random_bytes(5));
        $userId = User::insert([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $request->input('phone') ?: null,
            'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
            'status' => 'active',
        ]);

        $role = $request->input('role', 'learner');
        User::assignRole($userId, $role);

        Mailer::send(
            $data['email'],
            $data['full_name'],
            'Your CPI staff account',
            "<p>Dear " . e($data['full_name']) . ",</p><p>An account has been created for you at Crawford Professionals Institute
             with the role of <strong>" . e($role) . "</strong>.</p>
             <p>Login: <a href=\"" . url('/login') . "\">" . url('/login') . "</a><br>Email: " . e($data['email'])
             . "<br>Temporary password: <strong>" . e($tempPassword) . "</strong></p>"
        );

        AuditLog::record('user.create', 'user', $userId, ['role' => $role]);
        $this->flash('success', 'User created and notified.');
        $this->redirect('/admin/users');
    }

    public function updateRoles(Request $request): void
    {
        $this->requirePermission('users.manage');
        $this->verifyCsrf($request);
        $userId = (int) $request->param('user');
        $user = User::find($userId);
        if (!$user) {
            $this->abort(404, 'User not found.');
            return;
        }

        $selected = array_map('strval', (array) $request->input('roles', []));
        $all = array_column(Role::all(), 'slug');
        foreach ($all as $slug) {
            if (in_array($slug, $selected, true)) {
                User::assignRole($userId, $slug);
            } else {
                User::removeRole($userId, $slug);
            }
        }

        AuditLog::record('user.roles_update', 'user', $userId, ['roles' => $selected]);
        $this->flash('success', 'Roles updated.');
        $this->redirect('/admin/users');
    }

    public function toggleStatus(Request $request): void
    {
        $this->requirePermission('users.manage');
        $this->verifyCsrf($request);
        $userId = (int) $request->param('user');
        $user = User::find($userId);
        if (!$user) {
            $this->abort(404, 'User not found.');
            return;
        }
        $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
        User::update($userId, ['status' => $newStatus]);
        AuditLog::record('user.status', 'user', $userId, ['status' => $newStatus]);
        $this->flash('success', 'User status updated.');
        $this->redirect('/admin/users');
    }
}
