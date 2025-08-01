<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\ComplianceAuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplianceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        $stats = $this->getComplianceStats($user->company_id);
        $overdueCompliances = Compliance::where('company_id', $user->company_id)
            ->overdue()
            ->with('assignedTo')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
        
        $recentAudits = ComplianceAuditLog::where('company_id', $user->company_id)
            ->with(['compliance', 'auditor'])
            ->orderBy('audit_date', 'desc')
            ->limit(5)
            ->get();
        
        $complianceByType = $this->getComplianceByType($user->company_id);
        
        return view('compliance.index', compact('company', 'stats', 'overdueCompliances', 'recentAudits', 'complianceByType'));
    }

    public function compliances()
    {
        $user = Auth::user();
        $compliances = Compliance::where('company_id', $user->company_id)
            ->with(['assignedTo'])
            ->orderBy('due_date', 'asc')
            ->paginate(15);

        return view('compliance.compliances', compact('compliances'));
    }

    public function create()
    {
        $users = User::where('company_id', Auth::user()->company_id)
            ->whereIn('role', ['admin', 'hr', 'manager'])
            ->get();

        return view('compliance.create', compact('users'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create compliance requirements.');
        }

        $request->validate([
            'compliance_type' => 'required|in:' . implode(',', [
                Compliance::TYPE_TAX_COMPLIANCE,
                Compliance::TYPE_LABOR_LAW,
                Compliance::TYPE_BPJS_COMPLIANCE,
                Compliance::TYPE_DATA_PROTECTION,
                Compliance::TYPE_FINANCIAL_REPORTING,
                Compliance::TYPE_EMPLOYMENT_CONTRACTS,
                Compliance::TYPE_WORKPLACE_SAFETY,
                Compliance::TYPE_ANTI_DISCRIMINATION,
                Compliance::TYPE_OTHER
            ]),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'regulation_reference' => 'nullable|string|max:255',
            'effective_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:effective_date',
            'priority' => 'required|in:' . implode(',', [
                Compliance::PRIORITY_LOW,
                Compliance::PRIORITY_MEDIUM,
                Compliance::PRIORITY_HIGH,
                Compliance::PRIORITY_CRITICAL
            ]),
            'risk_level' => 'required|in:' . implode(',', [
                Compliance::RISK_LOW,
                Compliance::RISK_MEDIUM,
                Compliance::RISK_HIGH,
                Compliance::RISK_CRITICAL
            ]),
            'assigned_to' => 'nullable|exists:users,id',
            'documentation_required' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $compliance = Compliance::create([
            'company_id' => $user->company_id,
            'compliance_type' => $request->compliance_type,
            'title' => $request->title,
            'description' => $request->description,
            'regulation_reference' => $request->regulation_reference,
            'effective_date' => $request->effective_date,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'risk_level' => $request->risk_level,
            'assigned_to' => $request->assigned_to,
            'documentation_required' => $request->documentation_required,
            'notes' => $request->notes
        ]);

        $compliance->updateComplianceScore();

        return redirect()->route('compliance.compliances')
            ->with('success', 'Compliance requirement created successfully.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $compliance = Compliance::where('company_id', $user->company_id)
            ->with(['assignedTo', 'auditLogs.auditor', 'documents'])
            ->findOrFail($id);

        $auditLogs = $compliance->auditLogs()->orderBy('audit_date', 'desc')->get();
        $documents = $compliance->documents()->orderBy('created_at', 'desc')->get();

        return view('compliance.show', compact('compliance', 'auditLogs', 'documents'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $compliance = Compliance::where('company_id', $user->company_id)
            ->findOrFail($id);

        $users = User::where('company_id', $user->company_id)
            ->whereIn('role', ['admin', 'hr', 'manager'])
            ->get();

        return view('compliance.edit', compact('compliance', 'users'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update compliance requirements.');
        }

        $compliance = Compliance::where('company_id', $user->company_id)
            ->findOrFail($id);

        $request->validate([
            'compliance_type' => 'required|in:' . implode(',', [
                Compliance::TYPE_TAX_COMPLIANCE,
                Compliance::TYPE_LABOR_LAW,
                Compliance::TYPE_BPJS_COMPLIANCE,
                Compliance::TYPE_DATA_PROTECTION,
                Compliance::TYPE_FINANCIAL_REPORTING,
                Compliance::TYPE_EMPLOYMENT_CONTRACTS,
                Compliance::TYPE_WORKPLACE_SAFETY,
                Compliance::TYPE_ANTI_DISCRIMINATION,
                Compliance::TYPE_OTHER
            ]),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'regulation_reference' => 'nullable|string|max:255',
            'effective_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:effective_date',
            'status' => 'required|in:' . implode(',', [
                Compliance::STATUS_PENDING,
                Compliance::STATUS_IN_PROGRESS,
                Compliance::STATUS_COMPLETED,
                Compliance::STATUS_OVERDUE,
                Compliance::STATUS_EXEMPT,
                Compliance::STATUS_UNDER_REVIEW
            ]),
            'priority' => 'required|in:' . implode(',', [
                Compliance::PRIORITY_LOW,
                Compliance::PRIORITY_MEDIUM,
                Compliance::PRIORITY_HIGH,
                Compliance::PRIORITY_CRITICAL
            ]),
            'risk_level' => 'required|in:' . implode(',', [
                Compliance::RISK_LOW,
                Compliance::RISK_MEDIUM,
                Compliance::RISK_HIGH,
                Compliance::RISK_CRITICAL
            ]),
            'assigned_to' => 'nullable|exists:users,id',
            'completion_date' => 'nullable|date',
            'documentation_required' => 'nullable|array',
            'notes' => 'nullable|string',
            'last_audit_date' => 'nullable|date',
            'next_audit_date' => 'nullable|date|after:last_audit_date'
        ]);

        $compliance->update([
            'compliance_type' => $request->compliance_type,
            'title' => $request->title,
            'description' => $request->description,
            'regulation_reference' => $request->regulation_reference,
            'effective_date' => $request->effective_date,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'priority' => $request->priority,
            'risk_level' => $request->risk_level,
            'assigned_to' => $request->assigned_to,
            'completion_date' => $request->completion_date,
            'documentation_required' => $request->documentation_required,
            'notes' => $request->notes,
            'last_audit_date' => $request->last_audit_date,
            'next_audit_date' => $request->next_audit_date
        ]);

        $compliance->updateComplianceScore();

        return redirect()->route('compliance.compliances')
            ->with('success', 'Compliance requirement updated successfully.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete compliance requirements.');
        }

        $compliance = Compliance::where('company_id', $user->company_id)
            ->findOrFail($id);

        $compliance->delete();

        return redirect()->route('compliance.compliances')
            ->with('success', 'Compliance requirement deleted successfully.');
    }

    public function audits()
    {
        $user = Auth::user();
        $audits = ComplianceAuditLog::where('company_id', $user->company_id)
            ->with(['compliance', 'auditor'])
            ->orderBy('audit_date', 'desc')
            ->paginate(15);

        return view('compliance.audits', compact('audits'));
    }

    public function createAudit($complianceId)
    {
        $user = Auth::user();
        $compliance = Compliance::where('company_id', $user->company_id)
            ->findOrFail($complianceId);

        $auditors = User::where('company_id', $user->company_id)
            ->whereIn('role', ['admin', 'hr', 'manager'])
            ->get();

        return view('compliance.create-audit', compact('compliance', 'auditors'));
    }

    public function storeAudit(Request $request, $complianceId)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create audits.');
        }

        $compliance = Compliance::where('company_id', $user->company_id)
            ->findOrFail($complianceId);

        $request->validate([
            'audit_type' => 'required|in:' . implode(',', [
                ComplianceAuditLog::TYPE_INTERNAL,
                ComplianceAuditLog::TYPE_EXTERNAL,
                ComplianceAuditLog::TYPE_REGULATORY,
                ComplianceAuditLog::TYPE_SELF_ASSESSMENT
            ]),
            'audit_date' => 'required|date',
            'auditor_id' => 'nullable|exists:users,id',
            'findings' => 'nullable|array',
            'recommendations' => 'nullable|array',
            'compliance_score' => 'nullable|numeric|min:0|max:100',
            'risk_assessment' => 'nullable|array',
            'corrective_actions' => 'nullable|array',
            'follow_up_date' => 'nullable|date|after:audit_date',
            'status' => 'required|in:' . implode(',', [
                ComplianceAuditLog::STATUS_PENDING,
                ComplianceAuditLog::STATUS_IN_PROGRESS,
                ComplianceAuditLog::STATUS_COMPLETED,
                ComplianceAuditLog::STATUS_FAILED,
                ComplianceAuditLog::STATUS_OVERDUE
            ]),
            'notes' => 'nullable|string'
        ]);

        $audit = ComplianceAuditLog::create([
            'company_id' => $user->company_id,
            'compliance_id' => $compliance->id,
            'audit_type' => $request->audit_type,
            'audit_date' => $request->audit_date,
            'auditor_id' => $request->auditor_id,
            'findings' => $request->findings,
            'recommendations' => $request->recommendations,
            'compliance_score' => $request->compliance_score,
            'risk_assessment' => $request->risk_assessment,
            'corrective_actions' => $request->corrective_actions,
            'follow_up_date' => $request->follow_up_date,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        // Update compliance with audit information
        $compliance->update([
            'last_audit_date' => $request->audit_date,
            'compliance_score' => $request->compliance_score
        ]);

        return redirect()->route('compliance.show', $compliance->id)
            ->with('success', 'Audit created successfully.');
    }

    public function reports()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        $stats = $this->getComplianceStats($user->company_id);
        $complianceByType = $this->getComplianceByType($user->company_id);
        $complianceByStatus = $this->getComplianceByStatus($user->company_id);
        $complianceByPriority = $this->getComplianceByPriority($user->company_id);
        $complianceByRiskLevel = $this->getComplianceByRiskLevel($user->company_id);
        
        $monthlyTrends = $this->getMonthlyTrends($user->company_id);
        $overdueCompliances = Compliance::where('company_id', $user->company_id)
            ->overdue()
            ->with('assignedTo')
            ->get();
        
        $upcomingDeadlines = Compliance::where('company_id', $user->company_id)
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(30))
            ->whereNotIn('status', [Compliance::STATUS_COMPLETED, Compliance::STATUS_EXEMPT])
            ->with('assignedTo')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('compliance.reports', compact(
            'company', 'stats', 'complianceByType', 'complianceByStatus', 
            'complianceByPriority', 'complianceByRiskLevel', 'monthlyTrends',
            'overdueCompliances', 'upcomingDeadlines'
        ));
    }

    private function getComplianceStats($companyId)
    {
        $totalCompliances = Compliance::where('company_id', $companyId)->count();
        $completedCompliances = Compliance::where('company_id', $companyId)
            ->where('status', Compliance::STATUS_COMPLETED)
            ->count();
        $overdueCompliances = Compliance::where('company_id', $companyId)
            ->overdue()
            ->count();
        $averageScore = Compliance::where('company_id', $companyId)
            ->whereNotNull('compliance_score')
            ->avg('compliance_score');

        return [
            'total_compliances' => $totalCompliances,
            'completed_compliances' => $completedCompliances,
            'overdue_compliances' => $overdueCompliances,
            'completion_rate' => $totalCompliances > 0 ? round(($completedCompliances / $totalCompliances) * 100, 2) : 0,
            'average_score' => round($averageScore ?? 0, 2)
        ];
    }

    private function getComplianceByType($companyId)
    {
        return Compliance::where('company_id', $companyId)
            ->select('compliance_type', DB::raw('count(*) as count'))
            ->groupBy('compliance_type')
            ->get()
            ->pluck('count', 'compliance_type')
            ->toArray();
    }

    private function getComplianceByStatus($companyId)
    {
        return Compliance::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getComplianceByPriority($companyId)
    {
        return Compliance::where('company_id', $companyId)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();
    }

    private function getComplianceByRiskLevel($companyId)
    {
        return Compliance::where('company_id', $companyId)
            ->select('risk_level', DB::raw('count(*) as count'))
            ->groupBy('risk_level')
            ->get()
            ->pluck('count', 'risk_level')
            ->toArray();
    }

    private function getMonthlyTrends($companyId)
    {
        return Compliance::where('company_id', $companyId)
            ->where('created_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }
} 