<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExternalIntegration;
use App\Models\Company;

class ExternalIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // HRIS Integration
            ExternalIntegration::create([
                'company_id' => $company->id,
                'integration_type' => ExternalIntegration::TYPE_HRIS,
                'name' => 'Company HRIS System',
                'api_endpoint' => 'https://api.hris.company.com/v1',
                'api_key' => 'hris_api_key_' . $company->id,
                'api_secret' => 'hris_secret_' . $company->id,
                'username' => 'hris_user',
                'password' => 'hris_password',
                'is_active' => true,
                'sync_frequency' => ExternalIntegration::FREQ_DAILY,
                'config_data' => [
                    'sync_employees' => true,
                    'sync_departments' => true,
                    'sync_positions' => true,
                    'webhook_url' => 'https://payroll.company.com/webhooks/hris'
                ],
                'status' => ExternalIntegration::STATUS_ACTIVE,
                'notes' => 'HRIS integration for employee data synchronization'
            ]);

            // Accounting Integration
            ExternalIntegration::create([
                'company_id' => $company->id,
                'integration_type' => ExternalIntegration::TYPE_ACCOUNTING,
                'name' => 'QuickBooks Integration',
                'api_endpoint' => 'https://quickbooks.api.intuit.com/v3',
                'api_key' => 'qb_api_key_' . $company->id,
                'api_secret' => 'qb_secret_' . $company->id,
                'username' => 'qb_user',
                'password' => 'qb_password',
                'is_active' => true,
                'sync_frequency' => ExternalIntegration::FREQ_DAILY,
                'config_data' => [
                    'company_id' => 'qb_company_' . $company->id,
                    'sync_journal_entries' => true,
                    'sync_vendors' => true,
                    'sync_accounts' => true,
                    'auto_create_vendors' => true
                ],
                'status' => ExternalIntegration::STATUS_ACTIVE,
                'notes' => 'QuickBooks integration for accounting data'
            ]);

            // Government Portal Integration
            ExternalIntegration::create([
                'company_id' => $company->id,
                'integration_type' => ExternalIntegration::TYPE_GOVERNMENT,
                'name' => 'Government Portal',
                'api_endpoint' => 'https://api.government.gov.id/v1',
                'api_key' => 'gov_api_key_' . $company->id,
                'api_secret' => 'gov_secret_' . $company->id,
                'username' => 'gov_user',
                'password' => 'gov_password',
                'is_active' => false,
                'sync_frequency' => ExternalIntegration::FREQ_WEEKLY,
                'config_data' => [
                    'company_tax_id' => '123456789',
                    'sync_compliance_reports' => true,
                    'sync_employee_data' => true,
                    'auto_submit_reports' => false
                ],
                'status' => ExternalIntegration::STATUS_INACTIVE,
                'notes' => 'Government portal integration for compliance reporting'
            ]);

            // BPJS Online Integration
            ExternalIntegration::create([
                'company_id' => $company->id,
                'integration_type' => ExternalIntegration::TYPE_BPJS,
                'name' => 'BPJS Online System',
                'api_endpoint' => 'https://api.bpjs-kesehatan.go.id/v1',
                'api_key' => 'bpjs_api_key_' . $company->id,
                'api_secret' => 'bpjs_secret_' . $company->id,
                'username' => 'bpjs_user',
                'password' => 'bpjs_password',
                'is_active' => true,
                'sync_frequency' => ExternalIntegration::FREQ_MONTHLY,
                'config_data' => [
                    'company_bpjs_id' => 'BPJS' . $company->id,
                    'sync_employee_data' => true,
                    'sync_contribution_data' => true,
                    'sync_claim_data' => false,
                    'auto_submit_contributions' => true
                ],
                'status' => ExternalIntegration::STATUS_ACTIVE,
                'notes' => 'BPJS online integration for health insurance data'
            ]);

            // Tax Office Integration
            ExternalIntegration::create([
                'company_id' => $company->id,
                'integration_type' => ExternalIntegration::TYPE_TAX_OFFICE,
                'name' => 'Tax Office System',
                'api_endpoint' => 'https://api.pajak.go.id/v1',
                'api_key' => 'tax_api_key_' . $company->id,
                'api_secret' => 'tax_secret_' . $company->id,
                'username' => 'tax_user',
                'password' => 'tax_password',
                'is_active' => false,
                'sync_frequency' => ExternalIntegration::FREQ_MONTHLY,
                'config_data' => [
                    'company_tax_id' => 'NPWP' . $company->id,
                    'sync_tax_reports' => true,
                    'sync_employee_tax_data' => true,
                    'auto_submit_reports' => false,
                    'certificate_generation' => true
                ],
                'status' => ExternalIntegration::STATUS_INACTIVE,
                'notes' => 'Tax office integration for tax reporting and compliance'
            ]);
        }
    }
} 