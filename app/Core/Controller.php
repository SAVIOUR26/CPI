<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts.app'): void
    {
        $data['auth_user'] = Auth::user();
        $data['auth_roles'] = Auth::roles();
        echo (new View())->render($view, $data, $layout);
    }

    protected function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($ref);
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $this->view('errors.generic', ['code' => $code, 'message' => $message], 'layouts.app');
        exit;
    }

    protected function requireAuth(): array
    {
        if (!Auth::check()) {
            Session::put('intended_url', $_SERVER['REQUEST_URI'] ?? '/');
            $this->redirect('/login');
        }
        return Auth::user();
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        if (!Auth::hasRole(...$roles)) {
            $this->abort(403, "You don't have access to this page.");
        }
    }

    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();
        if (!Auth::can($permission)) {
            $this->abort(403, "You don't have access to this page.");
        }
    }

    protected function verifyCsrf(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf'))) {
            $this->abort(419, 'Your session expired. Please try again.');
        }
    }

    protected function validate(Request $request, array $rules): array
    {
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            Session::flashInput($request->all());
            Session::flash('errors', $v->errors());
            $this->back();
        }
        return $request->only(array_keys($rules));
    }
}
