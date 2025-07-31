<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Performance;
use App\Models\KPI;
use App\Models\Goal;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Display performance dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        // Get performance statistics
        $stats = $this->getPerformanceStats($user->company_id);
        
        // Get recent performance reviews
        $recentReviews = Performance::where('company_id', $user->company_id)
            ->with(['employee', 'reviewer'])
            ->orderBy('review_date', 'desc')
            ->limit(5)
            ->get();
        
        // Get top performers
        $topPerformers = $this->getTopPerformers($user->company_id);
        
        return view('performance.index', compact('company', 'stats', 'recentReviews', 'topPerformers'));
    }

    /**
     * Display KPI management.
     */
    public function kpi()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to access KPI management.');
        }

        $kpis = KPI::where('company_id', $user->company_id)
            ->with(['employee', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();
        
        return view('performance.kpi', compact('kpis', 'employees'));
    }

    /**
     * Store new KPI.
     */
    public function storeKPI(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create KPIs.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kpi_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
            'period' => 'required|string|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        KPI::create([
            'employee_id' => $request->employee_id,
            'company_id' => $user->company_id,
            'kpi_name' => $request->kpi_name,
            'description' => $request->description,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value,
            'unit' => $request->unit,
            'weight' => $request->weight,
            'period' => $request->period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => $user->id,
        ]);

        return redirect()->route('performance.kpi')
            ->with('success', 'KPI created successfully.');
    }

    /**
     * Update KPI.
     */
    public function updateKPI(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update KPIs.');
        }

        $kpi = KPI::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $request->validate([
            'kpi_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
            'period' => 'required|string|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $kpi->update($request->only([
            'kpi_name', 'description', 'target_value', 'current_value',
            'unit', 'weight', 'period', 'start_date', 'end_date'
        ]));

        return redirect()->route('performance.kpi')
            ->with('success', 'KPI updated successfully.');
    }

    /**
     * Display performance appraisal.
     */
    public function appraisal()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to access performance appraisal.');
        }

        $appraisals = Performance::where('company_id', $user->company_id)
            ->with(['employee', 'reviewer'])
            ->orderBy('review_date', 'desc')
            ->paginate(15);
        
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();
        
        return view('performance.appraisal', compact('appraisals', 'employees'));
    }

    /**
     * Create new performance appraisal.
     */
    public function createAppraisal(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to create performance appraisals.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'review_period' => 'required|string|in:monthly,quarterly,yearly',
            'review_date' => 'required|date',
            'overall_rating' => 'required|numeric|min:1|max:5',
            'job_knowledge' => 'required|numeric|min:1|max:5',
            'quality_of_work' => 'required|numeric|min:1|max:5',
            'productivity' => 'required|numeric|min:1|max:5',
            'teamwork' => 'required|numeric|min:1|max:5',
            'communication' => 'required|numeric|min:1|max:5',
            'initiative' => 'required|numeric|min:1|max:5',
            'attendance' => 'required|numeric|min:1|max:5',
            'strengths' => 'required|string|max:1000',
            'weaknesses' => 'required|string|max:1000',
            'improvement_plan' => 'required|string|max:1000',
            'comments' => 'nullable|string|max:1000',
        ]);

        Performance::create([
            'employee_id' => $request->employee_id,
            'company_id' => $user->company_id,
            'reviewer_id' => $user->id,
            'review_period' => $request->review_period,
            'review_date' => $request->review_date,
            'overall_rating' => $request->overall_rating,
            'job_knowledge' => $request->job_knowledge,
            'quality_of_work' => $request->quality_of_work,
            'productivity' => $request->productivity,
            'teamwork' => $request->teamwork,
            'communication' => $request->communication,
            'initiative' => $request->initiative,
            'attendance' => $request->attendance,
            'strengths' => $request->strengths,
            'weaknesses' => $request->weaknesses,
            'improvement_plan' => $request->improvement_plan,
            'comments' => $request->comments,
        ]);

        return redirect()->route('performance.appraisal')
            ->with('success', 'Performance appraisal created successfully.');
    }

    /**
     * Display performance bonus calculation.
     */
    public function bonus()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to access performance bonus.');
        }

        $bonuses = $this->calculatePerformanceBonuses($user->company_id);
        
        return view('performance.bonus', compact('bonuses'));
    }

    /**
     * Calculate performance bonus for all employees.
     */
    public function calculateBonus(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to calculate performance bonuses.');
        }

        $request->validate([
            'period' => 'required|string|in:monthly,quarterly,yearly',
            'year' => 'required|integer|min:2020',
            'month' => 'required_if:period,monthly|integer|between:1,12',
            'quarter' => 'required_if:period,quarterly|integer|between:1,4',
        ]);

        $bonuses = $this->calculatePerformanceBonuses(
            $user->company_id,
            $request->period,
            $request->year,
            $request->month ?? null,
            $request->quarter ?? null
        );

        return view('performance.bonus', compact('bonuses'));
    }

    /**
     * Display goal setting.
     */
    public function goals()
    {
        $user = Auth::user();
        
        $goals = Goal::where('company_id', $user->company_id)
            ->with(['employee', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();
        
        return view('performance.goals', compact('goals', 'employees'));
    }

    /**
     * Store new goal.
     */
    public function storeGoal(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'goal_title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:not_started,in_progress,completed,overdue',
        ]);

        Goal::create([
            'employee_id' => $request->employee_id,
            'company_id' => $user->company_id,
            'assigned_by' => $user->id,
            'goal_title' => $request->goal_title,
            'description' => $request->description,
            'target_value' => $request->target_value,
            'current_value' => $request->current_value,
            'unit' => $request->unit,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        return redirect()->route('performance.goals')
            ->with('success', 'Goal created successfully.');
    }

    /**
     * Update goal.
     */
    public function updateGoal(Request $request, $id)
    {
        $user = Auth::user();
        
        $goal = Goal::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $request->validate([
            'goal_title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'target_value' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:not_started,in_progress,completed,overdue',
        ]);

        $goal->update($request->only([
            'goal_title', 'description', 'target_value', 'current_value',
            'unit', 'priority', 'start_date', 'due_date', 'status'
        ]));

        return redirect()->route('performance.goals')
            ->with('success', 'Goal updated successfully.');
    }

    /**
     * Display performance reports.
     */
    public function reports()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to access performance reports.');
        }

        $reports = $this->generatePerformanceReports($user->company_id);
        
        return view('performance.reports', compact('reports'));
    }

    /**
     * Get performance statistics.
     */
    private function getPerformanceStats($companyId)
    {
        $totalEmployees = Employee::where('company_id', $companyId)->count();
        $totalAppraisals = Performance::where('company_id', $companyId)->count();
        $avgRating = Performance::where('company_id', $companyId)->avg('overall_rating');
        $topPerformers = Performance::where('company_id', $companyId)
            ->where('overall_rating', '>=', 4.0)
            ->count();

        return [
            'total_employees' => $totalEmployees,
            'total_appraisals' => $totalAppraisals,
            'average_rating' => round($avgRating, 2),
            'top_performers' => $topPerformers,
            'performance_rate' => $totalEmployees > 0 ? round(($totalAppraisals / $totalEmployees) * 100, 1) : 0,
        ];
    }

    /**
     * Get top performers.
     */
    private function getTopPerformers($companyId)
    {
        return Performance::where('company_id', $companyId)
            ->with(['employee'])
            ->orderBy('overall_rating', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Calculate performance bonuses.
     */
    private function calculatePerformanceBonuses($companyId, $period = 'yearly', $year = null, $month = null, $quarter = null)
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $bonuses = [];

        foreach ($employees as $employee) {
            // Get latest performance rating
            $performance = Performance::where('employee_id', $employee->id)
                ->where('company_id', $companyId)
                ->orderBy('review_date', 'desc')
                ->first();

            if ($performance) {
                $rating = $performance->overall_rating;
                $basicSalary = $employee->basic_salary;
                
                // Calculate bonus based on rating
                $bonusPercentage = $this->getBonusPercentage($rating);
                $bonusAmount = ($basicSalary * $bonusPercentage) / 100;

                $bonuses[] = [
                    'employee' => $employee,
                    'performance' => $performance,
                    'rating' => $rating,
                    'basic_salary' => $basicSalary,
                    'bonus_percentage' => $bonusPercentage,
                    'bonus_amount' => $bonusAmount,
                ];
            }
        }

        return $bonuses;
    }

    /**
     * Get bonus percentage based on rating.
     */
    private function getBonusPercentage($rating)
    {
        if ($rating >= 4.5) return 20; // Outstanding
        if ($rating >= 4.0) return 15; // Excellent
        if ($rating >= 3.5) return 10; // Good
        if ($rating >= 3.0) return 5;  // Satisfactory
        return 0; // Below satisfactory
    }

    /**
     * Generate performance reports.
     */
    private function generatePerformanceReports($companyId)
    {
        $reports = [];

        // Department performance report
        $departmentStats = Employee::where('company_id', $companyId)
            ->selectRaw('department, COUNT(*) as total_employees, AVG(employees.basic_salary) as avg_salary')
            ->groupBy('department')
            ->get();

        // Performance rating distribution
        $ratingDistribution = Performance::where('company_id', $companyId)
            ->selectRaw('
                CASE 
                    WHEN overall_rating >= 4.5 THEN "Outstanding (4.5-5.0)"
                    WHEN overall_rating >= 4.0 THEN "Excellent (4.0-4.4)"
                    WHEN overall_rating >= 3.5 THEN "Good (3.5-3.9)"
                    WHEN overall_rating >= 3.0 THEN "Satisfactory (3.0-3.4)"
                    ELSE "Below Satisfactory (<3.0)"
                END as rating_category,
                COUNT(*) as count
            ')
            ->groupBy('rating_category')
            ->get();

        // Monthly performance trend
        $monthlyTrend = Performance::where('company_id', $companyId)
            ->whereYear('review_date', date('Y'))
            ->selectRaw('MONTH(review_date) as month, AVG(overall_rating) as avg_rating')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $reports = [
            'department_stats' => $departmentStats,
            'rating_distribution' => $ratingDistribution,
            'monthly_trend' => $monthlyTrend,
        ];

        return $reports;
    }
} 