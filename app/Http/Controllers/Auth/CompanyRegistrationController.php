<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class CompanyRegistrationController extends Controller
{
    /**
     * Show the company registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.company-register');
    }

    /**
     * Handle company registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string',
            'company_city' => 'required|string|max:100',
            'company_province' => 'required|string|max:100',
            'company_postal_code' => 'required|string|max:10',
            'company_website' => 'nullable|url|max:255',
            'company_tax_number' => 'nullable|string|max:50',
            'company_business_number' => 'nullable|string|max:50',
            
            // Owner Information
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_phone' => 'required|string|max:20',
            'owner_position' => 'required|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            
            // Terms and conditions
            'terms_accepted' => 'required|accepted',
        ], [
            'company_name.required' => 'Nama perusahaan wajib diisi',
            'company_email.required' => 'Email perusahaan wajib diisi',
            'company_email.unique' => 'Email perusahaan sudah terdaftar',
            'company_phone.required' => 'Nomor telepon perusahaan wajib diisi',
            'company_address.required' => 'Alamat perusahaan wajib diisi',
            'company_city.required' => 'Kota wajib diisi',
            'company_province.required' => 'Provinsi wajib diisi',
            'company_postal_code.required' => 'Kode pos wajib diisi',
            'owner_name.required' => 'Nama pemilik wajib diisi',
            'owner_email.required' => 'Email pemilik wajib diisi',
            'owner_email.unique' => 'Email pemilik sudah terdaftar',
            'owner_phone.required' => 'Nomor telepon pemilik wajib diisi',
            'owner_position.required' => 'Jabatan pemilik wajib diisi',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'terms_accepted.required' => 'Anda harus menyetujui syarat dan ketentuan',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create company
            $company = Company::create([
                'name' => $request->company_name,
                'email' => $request->company_email,
                'phone' => $request->company_phone,
                'address' => $request->company_address,
                'city' => $request->company_city,
                'province' => $request->company_province,
                'postal_code' => $request->company_postal_code,
                'website' => $request->company_website,
                'tax_number' => $request->company_tax_number,
                'business_number' => $request->company_business_number,
                'status' => 'active',
                'subscription_plan' => 'free',
                'max_employees' => 10, // Free plan limit
                'is_trial' => true,
                'trial_ends_at' => now()->addDays(30),
            ]);

            // Create company owner user
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->password),
                'company_id' => $company->id,
                'role' => 'admin',
                'is_company_owner' => true,
                'phone' => $request->owner_phone,
                'position' => $request->owner_position,
                'department' => 'Management',
                'status' => 'active',
            ]);

            DB::commit();

            // Fire registered event
            event(new Registered($user));

            // Auto login
            auth()->login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Pendaftaran perusahaan berhasil! Selamat datang di Payroll KlikMedis.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Show the registration success page.
     */
    public function success()
    {
        return view('auth.registration-success');
    }

    /**
     * Check if company email is available.
     */
    public function checkCompanyEmail(Request $request)
    {
        $email = $request->email;
        $exists = Company::where('email', $email)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email perusahaan sudah terdaftar' : 'Email tersedia'
        ]);
    }

    /**
     * Check if owner email is available.
     */
    public function checkOwnerEmail(Request $request)
    {
        $email = $request->email;
        $exists = User::where('email', $email)->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email pemilik sudah terdaftar' : 'Email tersedia'
        ]);
    }
}
