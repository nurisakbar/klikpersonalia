<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use App\Models\Employee;
use App\Models\EmployeeBenefit;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BenefitController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        $stats = $this->getBenefitStats($user->company_id);
        $recentBenefits = Benefit::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $topBenefits = $this->getTopBenefits($user->company_id);
        
        return view('benefits.index', compact('company', 'stats', 'recentBenefits', 'topBenefits'));
    }

    public function benefits()
    {
        $user = Auth::user();
        $benefits = Benefit::where('company_id', $user->company_id)
            ->withCount(['employeeBenefits as active_employees' => function($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('benefits.benefits', compact('benefits'));
    }

    public function create()
    {
        return view('benefits.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create benefits.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'benefit_type' => 'required|in:' . implode(',', [
                Benefit::TYPE_HEALTH_INSURANCE,
                Benefit::TYPE_LIFE_INSURANCE,
                Benefit::TYPE_DISABILITY_INSURANCE,
                Benefit::TYPE_RETIREMENT_PLAN,
                Benefit::TYPE_EDUCATION_ASSISTANCE,
                Benefit::TYPE_MEAL_ALLOWANCE,
                Benefit::TYPE_TRANSPORT_ALLOWANCE,
                Benefit::TYPE_HOUSING_ALLOWANCE,
                Benefit::TYPE_OTHER
            ]),
            'cost_type' => 'required|in:' . implode(',', [
                Benefit::COST_TYPE_FIXED,
                Benefit::COST_TYPE_PERCENTAGE,
                Benefit::COST_TYPE_MIXED
            ]),
            'cost_amount' => 'nullable|numeric|min:0',
            'cost_percentage' => 'nullable|numeric|min:0|max:100',
            'provider' => 'nullable|string|max:255',
            'policy_number' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'eligibility_criteria' => 'nullable|array',
            'coverage_details' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $benefit = Benefit::create([
            'company_id' => $user->company_id,
            'name' => $request->name,
            'description' => $request->description,
            'benefit_type' => $request->benefit_type,
            'cost_type' => $request->cost_type,
            'cost_amount' => $request->cost_amount,
            'cost_percentage' => $request->cost_percentage,
            'provider' => $request->provider,
            'policy_number' => $request->policy_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active', true),
            'eligibility_criteria' => $request->eligibility_criteria,
            'coverage_details' => $request->coverage_details,
            'notes' => $request->notes
        ]);

        return redirect()->route('benefits.benefits')
            ->with('success', 'Benefit created successfully.');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $benefit = Benefit::where('company_id', $user->company_id)
            ->findOrFail($id);

        return view('benefits.edit', compact('benefit'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update benefits.');
        }

        $benefit = Benefit::where('company_id', $user->company_id)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:health,insurance,allowance,bonus,other',
            'amount' => 'nullable|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,yearly,one_time',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
        ]);

        $benefit->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'is_taxable' => $request->has('is_taxable'),
            'is_active' => $request->has('is_active'),
            'effective_date' => $request->effective_date,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->route('benefits.benefits')
            ->with('success', 'Benefit updated successfully.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete benefits.');
        }

        $benefit = Benefit::where('company_id', $user->company_id)
            ->findOrFail($id);

        // Check if benefit is assigned to any employees
        if ($benefit->employeeBenefits()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete benefit that is assigned to employees.');
        }

        $benefit->delete();

        return redirect()->route('benefits.benefits')
            ->with('success', 'Benefit deleted successfully.');
    }

    public function assignments()
    {
        $user = Auth::user();
        $assignments = EmployeeBenefit::where('company_id', $user->company_id)
            ->with(['employee', 'benefit', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();

        $benefits = Benefit::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();

        return view('benefits.assignments', compact('assignments', 'employees', 'benefits'));
    }

    public function assignBenefit(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to assign benefits.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'benefit_id' => 'required|exists:benefits,id',
            'enrollment_date' => 'required|date',
            'termination_date' => 'nullable|date|after:enrollment_date',
            'monthly_cost' => 'nullable|numeric|min:0',
            'employer_contribution' => 'nullable|numeric|min:0',
            'employee_contribution' => 'nullable|numeric|min:0',
            'coverage_amount' => 'nullable|numeric|min:0',
            'policy_number' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', [
                EmployeeBenefit::STATUS_ACTIVE,
                EmployeeBenefit::STATUS_INACTIVE,
                EmployeeBenefit::STATUS_PENDING,
                EmployeeBenefit::STATUS_TERMINATED,
                EmployeeBenefit::STATUS_SUSPENDED
            ]),
            'notes' => 'nullable|string'
        ]);

        // Check if assignment already exists
        $existingAssignment = EmployeeBenefit::where('company_id', $user->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('benefit_id', $request->benefit_id)
            ->where('status', EmployeeBenefit::STATUS_ACTIVE)
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Employee is already enrolled in this benefit.');
        }

        $employeeBenefit = EmployeeBenefit::create([
            'company_id' => $user->company_id,
            'employee_id' => $request->employee_id,
            'benefit_id' => $request->benefit_id,
            'enrollment_date' => $request->enrollment_date,
            'termination_date' => $request->termination_date,
            'monthly_cost' => $request->monthly_cost,
            'employer_contribution' => $request->employer_contribution,
            'employee_contribution' => $request->employee_contribution,
            'coverage_amount' => $request->coverage_amount,
            'policy_number' => $request->policy_number,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return redirect()->route('benefits.assignments')
            ->with('success', 'Benefit assigned successfully.');
    }

    public function updateAssignment(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update benefit assignments.');
        }

        $assignment = EmployeeBenefit::where('company_id', $user->company_id)
            ->findOrFail($id);

        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive,pending,expired',
            'notes' => 'nullable|string',
        ]);

        $assignment->update([
            'amount' => $request->amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('benefits.assignments')
            ->with('success', 'Benefit assignment updated successfully.');
    }

    public function removeAssignment($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to remove benefit assignments.');
        }

        $assignment = EmployeeBenefit::where('company_id', $user->company_id)
            ->findOrFail($id);

        $assignment->delete();

        return redirect()->route('benefits.assignments')
            ->with('success', 'Benefit assignment removed successfully.');
    }

    public function employeeBenefits($employeeId)
    {
        $user = Auth::user();
        
        // Check if user can view this employee's benefits
        if (!in_array($user->role, ['admin', 'hr']) && $user->employee_id !== $employeeId) {
            return redirect()->back()->with('error', 'You do not have permission to view this employee\'s benefits.');
        }

        $employee = Employee::where('company_id', $user->company_id)
            ->findOrFail($employeeId);

        $benefits = EmployeeBenefit::where('employee_id', $employeeId)
            ->where('company_id', $user->company_id)
            ->with(['benefit', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalValue = $benefits->where('status', 'active')->sum('amount');

        return view('benefits.employee-benefits', compact('employee', 'benefits', 'totalValue'));
    }

    public function reports()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to view benefit reports.');
        }

        $company = Company::find($user->company_id);
        $stats = $this->getBenefitStats($user->company_id);
        
        // Benefit type distribution
        $benefitTypes = Benefit::where('company_id', $user->company_id)
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('type')
            ->get();

        // Monthly benefit costs
        $monthlyCosts = EmployeeBenefit::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->selectRaw('MONTH(start_date) as month, YEAR(start_date) as year, SUM(amount) as total')
            ->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Top expensive benefits
        $expensiveBenefits = Benefit::where('company_id', $user->company_id)
            ->withCount(['employeeBenefits as active_employees' => function($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->get();

        return view('benefits.reports', compact('company', 'stats', 'benefitTypes', 'monthlyCosts', 'expensiveBenefits'));
    }

    // Helper methods
    private function getBenefitStats($companyId)
    {
        $totalBenefits = Benefit::where('company_id', $companyId)->count();
        $activeBenefits = Benefit::where('company_id', $companyId)->where('is_active', true)->count();
        $totalAssignments = EmployeeBenefit::where('company_id', $companyId)->count();
        $activeAssignments = EmployeeBenefit::where('company_id', $companyId)->where('status', 'active')->count();
        $totalCost = EmployeeBenefit::where('company_id', $companyId)->where('status', 'active')->sum('amount');
        $expiredBenefits = Benefit::where('company_id', $companyId)->where('expiry_date', '<', now())->count();

        return [
            'total_benefits' => $totalBenefits,
            'active_benefits' => $activeBenefits,
            'total_assignments' => $totalAssignments,
            'active_assignments' => $activeAssignments,
            'total_cost' => $totalCost,
            'expired_benefits' => $expiredBenefits,
        ];
    }

    private function getTopBenefits($companyId)
    {
        return Benefit::where('company_id', $companyId)
            ->withCount(['employeeBenefits as active_employees' => function($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('active_employees', 'desc')
            ->limit(5)
            ->get();
    }
} 