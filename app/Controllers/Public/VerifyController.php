<?php

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Certificate;

class VerifyController extends Controller
{
    public function form(Request $request): void
    {
        $this->view('public.verify', ['pageTitle' => 'Verify a Certificate — CPI']);
    }

    public function lookup(Request $request): void
    {
        $code = trim((string) $request->input('code'));
        if ($code === '') {
            $this->redirect('/verify');
            return;
        }
        $this->redirect('/verify/' . rawurlencode($code));
    }

    public function show(Request $request): void
    {
        $code = (string) $request->param('code');
        $certificate = Certificate::findByCode($code);
        $valid = $certificate && !$certificate['revoked'];

        $this->view('public.verify', [
            'pageTitle' => 'Certificate Verification — CPI',
            'code' => $code,
            'certificate' => $certificate,
            'valid' => $valid,
        ]);
    }
}
