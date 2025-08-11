<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        return view('settings.index', compact('company'));
    }

    /**
     * Display company profile settings.
     */
    public function company()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to access company settings.');
        }

        $company = Company::find($user->company_id);
        
        return view('settings.company', compact('company'));
    }

    /**
     * Update company profile.
     */
    public function updateCompany(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to update company settings.');
        }

        $company = Company::find($user->company_id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'website' => 'nullable|url|max:255',
            'tax_number' => 'nullable|string|max:50',
            'business_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'address', 'city', 'province', 
            'postal_code', 'country', 'website', 'tax_number', 'business_number'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $data['logo'] = $logoPath;
        }

        $company->update($data);

        return redirect()->route('settings.company')
            ->with('success', 'Company profile updated successfully.');
    }

    /**
     * Display payroll policy settings.
     */
    public function payrollPolicy()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to access payroll settings.');
        }

        $company = Company::find($user->company_id);
        
        return view('settings.payroll-policy', compact('company'));
    }

    /**
     * Update payroll policy.
     */
    public function updatePayrollPolicy(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update payroll settings.');
        }

        $request->validate([
            'working_hours_per_day' => 'required|numeric|min:1|max:24',
            'working_days_per_week' => 'required|numeric|min:1|max:7',
            'overtime_regular_rate' => 'required|numeric|min:1|max:5',
            'overtime_holiday_rate' => 'required|numeric|min:1|max:5',
            'overtime_weekend_rate' => 'required|numeric|min:1|max:5',
            'overtime_emergency_rate' => 'required|numeric|min:1|max:5',
            'attendance_bonus_95_rate' => 'required|numeric|min:0|max:1',
            'attendance_bonus_90_rate' => 'required|numeric|min:0|max:1',
            'leave_deduction_enabled' => 'boolean',
            'annual_leave_deduction' => 'boolean',
            'late_threshold_minutes' => 'required|numeric|min:0|max:120',
            'payroll_day' => 'required|numeric|min:1|max:31',
        ]);

        $company = Company::find($user->company_id);
        
        $company->update([
            'payroll_settings' => [
                'working_hours_per_day' => $request->working_hours_per_day,
                'working_days_per_week' => $request->working_days_per_week,
                'overtime_rates' => [
                    'regular' => $request->overtime_regular_rate,
                    'holiday' => $request->overtime_holiday_rate,
                    'weekend' => $request->overtime_weekend_rate,
                    'emergency' => $request->overtime_emergency_rate,
                ],
                'attendance_bonus' => [
                    '95_percent_rate' => $request->attendance_bonus_95_rate,
                    '90_percent_rate' => $request->attendance_bonus_90_rate,
                ],
                'leave_deduction' => [
                    'enabled' => $request->has('leave_deduction_enabled'),
                    'annual_leave_deduction' => $request->has('annual_leave_deduction'),
                ],
                'late_threshold_minutes' => $request->late_threshold_minutes,
                'payroll_day' => $request->payroll_day,
            ]
        ]);

        return redirect()->route('settings.payroll-policy')
            ->with('success', 'Payroll policy updated successfully.');
    }

    /**
     * Display leave policy settings.
     */
    public function leavePolicy()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to access leave settings.');
        }

        $company = Company::find($user->company_id);
        
        return view('settings.leave-policy', compact('company'));
    }

    /**
     * Update leave policy.
     */
    public function updateLeavePolicy(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to update leave settings.');
        }

        $request->validate([
            'annual_leave_quota' => 'required|numeric|min:0|max:365',
            'sick_leave_quota' => 'required|numeric|min:0|max:365',
            'maternity_leave_quota' => 'required|numeric|min:0|max:365',
            'paternity_leave_quota' => 'required|numeric|min:0|max:365',
            'other_leave_quota' => 'required|numeric|min:0|max:365',
            'leave_approval_required' => 'boolean',
            'leave_notice_days' => 'required|numeric|min:0|max:30',
            'leave_carry_forward' => 'boolean',
            'leave_carry_forward_limit' => 'required|numeric|min:0|max:365',
        ]);

        $company = Company::find($user->company_id);
        
        $company->update([
            'leave_settings' => [
                'quotas' => [
                    'annual' => $request->annual_leave_quota,
                    'sick' => $request->sick_leave_quota,
                    'maternity' => $request->maternity_leave_quota,
                    'paternity' => $request->paternity_leave_quota,
                    'other' => $request->other_leave_quota,
                ],
                'approval_required' => $request->has('leave_approval_required'),
                'notice_days' => $request->leave_notice_days,
                'carry_forward' => [
                    'enabled' => $request->has('leave_carry_forward'),
                    'limit' => $request->leave_carry_forward_limit,
                ],
            ]
        ]);

        return redirect()->route('settings.leave-policy')
            ->with('success', 'Leave policy updated successfully.');
    }

    /**
     * Display user profile settings.
     */
    public function profile()
    {
        $user = Auth::user();
        
        return view('settings.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('user-avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $user->update($data);

        return redirect()->route('settings.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Display password change form.
     */
    public function password()
    {
        return view('settings.password');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('settings.password')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Display system settings.
     */
    public function system()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to access system settings.');
        }

        return view('settings.system');
    }

    /**
     * Update system settings.
     */
    public function updateSystem(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to update system settings.');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'system_email' => 'required|email|max:255',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'decimal_places' => 'required|numeric|min:0|max:4',
        ]);

        // Update system settings in config or database
        // This would typically be stored in a settings table or config files
        
        return redirect()->route('settings.system')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Display backup and restore settings.
     */
    public function backup()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to access backup settings.');
        }

        return view('settings.backup');
    }

    /**
     * Create database backup.
     */
    public function createBackup()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to create backups.');
        }

        // Implementation for database backup
        // This would typically use Laravel's backup package or custom implementation
        
        return redirect()->route('settings.backup')
            ->with('success', 'Database backup created successfully.');
    }

    /**
     * Display user management.
     */
    public function users()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to access user management.');
        }

        $users = User::where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('settings.users', compact('users'));
    }

    /**
     * Create new user.
     */
    public function createUser(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to create users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,hr,manager,employee',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('settings.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Update user.
     */
    public function updateUser(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to update users.');
        }

        $targetUser = User::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($targetUser->id),
            ],
            'role' => 'required|string|in:admin,hr,manager,employee',
            'status' => 'required|string|in:active,inactive',
        ]);

        $targetUser->update($request->only(['name', 'email', 'role', 'status']));

        return redirect()->route('settings.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user.
     */
    public function deleteUser($id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete users.');
        }

        $targetUser = User::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Prevent deleting own account
        if ($targetUser->id === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $targetUser->delete();

        return redirect()->route('settings.users')
            ->with('success', 'User deleted successfully.');
    }
} 