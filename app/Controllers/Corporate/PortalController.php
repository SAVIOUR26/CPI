<?php

namespace App\Controllers\Corporate;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Intake;
use App\Models\Invoice;
use App\Models\Organization;

class PortalController extends Controller
{
    private function myOrganizations(): array
    {
        return Organization::forContact((int) Auth::id());
    }

    public function index(Request $request): void
    {
        $this->requireRole('corporate_contact', 'super_admin');
        $orgs = $this->myOrganizations();
        $orgIds = array_column($orgs, 'id');

        $cohorts = [];
        if ($orgIds) {
            $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
            $cohorts = Intake::query(
                "SELECT i.*, c.title AS course_title FROM intakes i JOIN courses c ON c.id = i.course_id
                 WHERE i.organization_id IN ($placeholders) ORDER BY i.start_date DESC",
                $orgIds
            );
        }

        $this->view('corporate.dashboard', [
            'pageTitle' => 'Corporate Portal — CPI',
            'orgs' => $orgs,
            'cohorts' => $cohorts,
        ], 'layouts.dashboard');
    }

    public function cohorts(Request $request): void
    {
        $this->requireRole('corporate_contact', 'super_admin');
        $orgs = $this->myOrganizations();
        $orgIds = array_column($orgs, 'id');
        $cohorts = [];

        if ($orgIds) {
            $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
            $cohorts = Intake::query(
                "SELECT i.*, c.title AS course_title FROM intakes i JOIN courses c ON c.id = i.course_id
                 WHERE i.organization_id IN ($placeholders) ORDER BY i.start_date DESC",
                $orgIds
            );
        }

        foreach ($cohorts as &$cohort) {
            $cohort['roster'] = Intake::roster((int) $cohort['id']);
        }
        unset($cohort);

        $this->view('corporate.cohorts', [
            'pageTitle' => 'Our Cohorts — CPI Corporate Portal',
            'cohorts' => $cohorts,
        ], 'layouts.dashboard');
    }

    public function invoices(Request $request): void
    {
        $this->requireRole('corporate_contact', 'super_admin');
        $orgs = $this->myOrganizations();
        $orgIds = array_column($orgs, 'id');
        $invoices = [];

        if ($orgIds) {
            $placeholders = implode(',', array_fill(0, count($orgIds), '?'));
            $invoices = Invoice::query(
                "SELECT * FROM invoices WHERE organization_id IN ($placeholders) ORDER BY created_at DESC",
                $orgIds
            );
        }

        $this->view('corporate.invoices', [
            'pageTitle' => 'Invoices — CPI Corporate Portal',
            'invoices' => $invoices,
        ], 'layouts.dashboard');
    }
}
