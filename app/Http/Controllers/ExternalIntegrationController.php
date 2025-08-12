<?php

namespace App\Http\Controllers;

use App\Models\ExternalIntegration;
use App\Models\IntegrationSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExternalIntegrationController extends Controller
{
    public function index()
    {
        $integrations = ExternalIntegration::where('company_id', Auth::user()->company_id)
            ->orderBy('integration_type')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $integrations->count(),
            'active' => $integrations->where('is_active', true)->count(),
            'error' => $integrations->where('status', 'error')->count(),
            'syncing' => $integrations->where('status', 'syncing')->count(),
        ];

        return view('integrations.index', compact('integrations', 'stats'));
    }

    public function create()
    {
        $integrationTypes = [
            ExternalIntegration::TYPE_HRIS => 'HRIS System',
            ExternalIntegration::TYPE_ACCOUNTING => 'Accounting System',
            ExternalIntegration::TYPE_GOVERNMENT => 'Government Portal',
            ExternalIntegration::TYPE_BPJS => 'BPJS Online',
            ExternalIntegration::TYPE_TAX_OFFICE => 'Tax Office'
        ];

        $syncFrequencies = [
            ExternalIntegration::FREQ_HOURLY => 'Hourly',
            ExternalIntegration::FREQ_DAILY => 'Daily',
            ExternalIntegration::FREQ_WEEKLY => 'Weekly',
            ExternalIntegration::FREQ_MONTHLY => 'Monthly'
        ];

        return view('integrations.create', compact('integrationTypes', 'syncFrequencies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'integration_type' => ['required', Rule::in([
                ExternalIntegration::TYPE_HRIS,
                ExternalIntegration::TYPE_ACCOUNTING,
                ExternalIntegration::TYPE_GOVERNMENT,
                ExternalIntegration::TYPE_BPJS,
                ExternalIntegration::TYPE_TAX_OFFICE
            ])],
            'name' => 'required|string|max:255',
            'api_endpoint' => 'nullable|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'sync_frequency' => 'required|integer|min:60',
            'config_data' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $integration = ExternalIntegration::create([
            'company_id' => Auth::user()->company_id,
            'integration_type' => $request->integration_type,
            'name' => $request->name,
            'api_endpoint' => $request->api_endpoint,
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
            'username' => $request->username,
            'password' => $request->password,
            'sync_frequency' => $request->sync_frequency,
            'config_data' => $request->config_data,
            'notes' => $request->notes,
            'status' => ExternalIntegration::STATUS_INACTIVE
        ]);

        return redirect()->route('integrations.index')
            ->with('success', 'Integration created successfully.');
    }

    public function show(ExternalIntegration $integration)
    {
        $this->authorize('view', $integration);

        $recentLogs = $integration->syncLogs()
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get();

        $syncStats = [
            'total_syncs' => $integration->syncLogs()->count(),
            'successful_syncs' => $integration->syncLogs()->where('status', IntegrationSyncLog::STATUS_SUCCESS)->count(),
            'failed_syncs' => $integration->syncLogs()->where('status', IntegrationSyncLog::STATUS_FAILED)->count(),
            'average_duration' => $integration->syncLogs()
                ->whereNotNull('sync_duration')
                ->avg('sync_duration')
        ];

        return view('integrations.show', compact('integration', 'recentLogs', 'syncStats'));
    }

    public function edit(ExternalIntegration $integration)
    {
        $this->authorize('update', $integration);

        $integrationTypes = [
            ExternalIntegration::TYPE_HRIS => 'HRIS System',
            ExternalIntegration::TYPE_ACCOUNTING => 'Accounting System',
            ExternalIntegration::TYPE_GOVERNMENT => 'Government Portal',
            ExternalIntegration::TYPE_BPJS => 'BPJS Online',
            ExternalIntegration::TYPE_TAX_OFFICE => 'Tax Office'
        ];

        $syncFrequencies = [
            ExternalIntegration::FREQ_HOURLY => 'Hourly',
            ExternalIntegration::FREQ_DAILY => 'Daily',
            ExternalIntegration::FREQ_WEEKLY => 'Weekly',
            ExternalIntegration::FREQ_MONTHLY => 'Monthly'
        ];

        return view('integrations.edit', compact('integration', 'integrationTypes', 'syncFrequencies'));
    }

    public function update(Request $request, ExternalIntegration $integration)
    {
        $this->authorize('update', $integration);

        $validator = Validator::make($request->all(), [
            'integration_type' => ['required', Rule::in([
                ExternalIntegration::TYPE_HRIS,
                ExternalIntegration::TYPE_ACCOUNTING,
                ExternalIntegration::TYPE_GOVERNMENT,
                ExternalIntegration::TYPE_BPJS,
                ExternalIntegration::TYPE_TAX_OFFICE
            ])],
            'name' => 'required|string|max:255',
            'api_endpoint' => 'nullable|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'sync_frequency' => 'required|integer|min:60',
            'config_data' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $integration->update([
            'integration_type' => $request->integration_type,
            'name' => $request->name,
            'api_endpoint' => $request->api_endpoint,
            'api_key' => $request->api_key,
            'api_secret' => $request->api_secret,
            'username' => $request->username,
            'password' => $request->password,
            'sync_frequency' => $request->sync_frequency,
            'config_data' => $request->config_data,
            'notes' => $request->notes
        ]);

        return redirect()->route('integrations.index')
            ->with('success', 'Integration updated successfully.');
    }

    public function destroy(ExternalIntegration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return redirect()->route('integrations.index')
            ->with('success', 'Integration deleted successfully.');
    }

    public function toggleStatus(ExternalIntegration $integration)
    {
        $this->authorize('update', $integration);

        $integration->update([
            'is_active' => !$integration->is_active,
            'status' => $integration->is_active ? ExternalIntegration::STATUS_INACTIVE : ExternalIntegration::STATUS_INACTIVE
        ]);

        return redirect()->route('integrations.index')
            ->with('success', 'Integration status updated successfully.');
    }

    public function testConnection(ExternalIntegration $integration)
    {
        $this->authorize('update', $integration);

        try {
            // Simulate connection test
            $success = $this->performConnectionTest($integration);
            
            if ($success) {
                $integration->update([
                    'status' => ExternalIntegration::STATUS_ACTIVE,
                    'error_message' => null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connection test successful!'
                ]);
            } else {
                $integration->update([
                    'status' => ExternalIntegration::STATUS_ERROR,
                    'error_message' => 'Connection test failed'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed. Please check your configuration.'
                ]);
            }
        } catch (\Exception $e) {
            $integration->update([
                'status' => ExternalIntegration::STATUS_ERROR,
                'error_message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    public function syncNow(ExternalIntegration $integration, Request $request)
    {
        $this->authorize('update', $integration);

        $syncType = $request->get('sync_type', IntegrationSyncLog::SYNC_EMPLOYEE);

        // Create sync log
        $syncLog = IntegrationSyncLog::create([
            'company_id' => Auth::user()->company_id,
            'external_integration_id' => $integration->id,
            'sync_type' => $syncType,
            'status' => IntegrationSyncLog::STATUS_RUNNING,
            'started_at' => now(),
            'triggered_by' => Auth::id()
        ]);

        try {
            // Simulate sync process
            $result = $this->performSync($integration, $syncType);
            
            $syncLog->markAsCompleted(
                $result['success'] ? IntegrationSyncLog::STATUS_SUCCESS : IntegrationSyncLog::STATUS_FAILED,
                $result['success_count'] ?? 0,
                $result['failed_count'] ?? 0,
                $result['error_message'] ?? null
            );

            $integration->update([
                'last_sync_at' => now(),
                'status' => $result['success'] ? ExternalIntegration::STATUS_ACTIVE : ExternalIntegration::STATUS_ERROR,
                'error_message' => $result['error_message'] ?? null
            ]);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Sync completed successfully!' : 'Sync failed: ' . ($result['error_message'] ?? 'Unknown error'),
                'sync_log_id' => $syncLog->id
            ]);

        } catch (\Exception $e) {
            $syncLog->markAsCompleted(
                IntegrationSyncLog::STATUS_FAILED,
                0,
                0,
                $e->getMessage()
            );

            $integration->update([
                'status' => ExternalIntegration::STATUS_ERROR,
                'error_message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    public function logs(ExternalIntegration $integration)
    {
        $this->authorize('view', $integration);

        $logs = $integration->syncLogs()
            ->with('triggeredByUser')
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('integrations.logs', compact('integration', 'logs'));
    }

    private function performConnectionTest(ExternalIntegration $integration)
    {
        // Simulate connection test based on integration type
        switch ($integration->integration_type) {
            case ExternalIntegration::TYPE_HRIS:
                return $this->testHrisConnection($integration);
            case ExternalIntegration::TYPE_ACCOUNTING:
                return $this->testAccountingConnection($integration);
            case ExternalIntegration::TYPE_GOVERNMENT:
                return $this->testGovernmentConnection($integration);
            case ExternalIntegration::TYPE_BPJS:
                return $this->testBpjsConnection($integration);
            case ExternalIntegration::TYPE_TAX_OFFICE:
                return $this->testTaxOfficeConnection($integration);
            default:
                return false;
        }
    }

    private function performSync(ExternalIntegration $integration, $syncType)
    {
        // Simulate sync process based on integration type and sync type
        switch ($integration->integration_type) {
            case ExternalIntegration::TYPE_HRIS:
                return $this->syncHrisData($integration, $syncType);
            case ExternalIntegration::TYPE_ACCOUNTING:
                return $this->syncAccountingData($integration, $syncType);
            case ExternalIntegration::TYPE_GOVERNMENT:
                return $this->syncGovernmentData($integration, $syncType);
            case ExternalIntegration::TYPE_BPJS:
                return $this->syncBpjsData($integration, $syncType);
            case ExternalIntegration::TYPE_TAX_OFFICE:
                return $this->syncTaxOfficeData($integration, $syncType);
            default:
                return ['success' => false, 'error_message' => 'Unknown integration type'];
        }
    }

    // Placeholder methods for connection tests
    private function testHrisConnection($integration) { return rand(0, 1) == 1; }
    private function testAccountingConnection($integration) { return rand(0, 1) == 1; }
    private function testGovernmentConnection($integration) { return rand(0, 1) == 1; }
    private function testBpjsConnection($integration) { return rand(0, 1) == 1; }
    private function testTaxOfficeConnection($integration) { return rand(0, 1) == 1; }

    // Placeholder methods for sync operations
    private function syncHrisData($integration, $syncType) { 
        return ['success' => rand(0, 1) == 1, 'success_count' => rand(5, 50), 'failed_count' => rand(0, 5)]; 
    }
    private function syncAccountingData($integration, $syncType) { 
        return ['success' => rand(0, 1) == 1, 'success_count' => rand(5, 50), 'failed_count' => rand(0, 5)]; 
    }
    private function syncGovernmentData($integration, $syncType) { 
        return ['success' => rand(0, 1) == 1, 'success_count' => rand(5, 50), 'failed_count' => rand(0, 5)]; 
    }
    private function syncBpjsData($integration, $syncType) { 
        return ['success' => rand(0, 1) == 1, 'success_count' => rand(5, 50), 'failed_count' => rand(0, 5)]; 
    }
    private function syncTaxOfficeData($integration, $syncType) { 
        return ['success' => rand(0, 1) == 1, 'success_count' => rand(5, 50), 'failed_count' => rand(0, 5)]; 
    }
} 