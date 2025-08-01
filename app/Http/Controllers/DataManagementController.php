<?php

namespace App\Http\Controllers;

use App\Models\DataArchive;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Tax;
use App\Models\Bpjs;
use App\Models\Benefit;
use App\Models\Performance;
use App\Models\Compliance;
use App\Models\ComplianceAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DataManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        $stats = $this->getDataManagementStats($user->company_id);
        $recentArchives = DataArchive::where('company_id', $user->company_id)
            ->with('archivedBy')
            ->orderBy('archive_date', 'desc')
            ->limit(10)
            ->get();
        
        $expiringArchives = DataArchive::where('company_id', $user->company_id)
            ->expiringSoon(30)
            ->with('archivedBy')
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get();
        
        $archiveByType = $this->getArchiveByType($user->company_id);
        
        return view('data-management.index', compact('company', 'stats', 'recentArchives', 'expiringArchives', 'archiveByType'));
    }

    public function archives()
    {
        $user = Auth::user();
        $archives = DataArchive::where('company_id', $user->company_id)
            ->with('archivedBy')
            ->orderBy('archive_date', 'desc')
            ->paginate(20);

        return view('data-management.archives', compact('archives'));
    }

    public function createArchive(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create archives.');
        }

        $request->validate([
            'archive_type' => 'required|in:' . implode(',', [
                DataArchive::TYPE_EMPLOYEE,
                DataArchive::TYPE_PAYROLL,
                DataArchive::TYPE_ATTENDANCE,
                DataArchive::TYPE_LEAVE,
                DataArchive::TYPE_OVERTIME,
                DataArchive::TYPE_TAX,
                DataArchive::TYPE_BPJS,
                DataArchive::TYPE_BENEFIT,
                DataArchive::TYPE_PERFORMANCE,
                DataArchive::TYPE_COMPLIANCE,
                DataArchive::TYPE_AUDIT
            ]),
            'table_name' => 'required|string',
            'record_ids' => 'required|array',
            'retention_period' => 'required|integer',
            'archive_reason' => 'required|string'
        ]);

        $archivedCount = 0;
        $errors = [];

        foreach ($request->record_ids as $recordId) {
            try {
                $this->archiveRecord(
                    $user->company_id,
                    $request->archive_type,
                    $request->table_name,
                    $recordId,
                    $request->retention_period,
                    $request->archive_reason,
                    $user->id
                );
                $archivedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to archive record {$recordId}: " . $e->getMessage();
            }
        }

        if ($archivedCount > 0) {
            $message = "Successfully archived {$archivedCount} records.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            return redirect()->route('data-management.archives')->with('success', $message);
        } else {
            return redirect()->back()->with('error', 'Failed to archive any records. ' . implode(', ', $errors));
        }
    }

    public function restoreArchive($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to restore archives.');
        }

        $archive = DataArchive::where('company_id', $user->company_id)
            ->findOrFail($id);

        try {
            $this->restoreRecord($archive);
            $archive->markAsRestored();

            return redirect()->route('data-management.archives')
                ->with('success', 'Archive restored successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore archive: ' . $e->getMessage());
        }
    }

    public function deleteArchive($id)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete archives.');
        }

        $archive = DataArchive::where('company_id', $user->company_id)
            ->findOrFail($id);

        try {
            // Delete file if exists
            if ($archive->file_path && Storage::exists($archive->file_path)) {
                Storage::delete($archive->file_path);
            }

            $archive->markAsDeleted();

            return redirect()->route('data-management.archives')
                ->with('success', 'Archive deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete archive: ' . $e->getMessage());
        }
    }

    public function backup()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to create backups.');
        }

        try {
            $backupData = $this->createBackup($user->company_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'data' => $backupData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function restore(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to restore backups.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:sql,zip'
        ]);

        try {
            $this->restoreBackup($request->file('backup_file'), $user->company_id);
            
            return redirect()->route('data-management.index')
                ->with('success', 'Backup restored successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore backup: ' . $e->getMessage());
        }
    }

    public function cleanup()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to perform cleanup.');
        }

        try {
            $cleanedCount = $this->performCleanup($user->company_id);
            
            return redirect()->route('data-management.index')
                ->with('success', "Cleanup completed. {$cleanedCount} expired archives processed.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to perform cleanup: ' . $e->getMessage());
        }
    }

    public function reports()
    {
        $user = Auth::user();
        $company = Company::find($user->company_id);
        
        $stats = $this->getDataManagementStats($user->company_id);
        $archiveByType = $this->getArchiveByType($user->company_id);
        $archiveByStatus = $this->getArchiveByStatus($user->company_id);
        $monthlyTrends = $this->getMonthlyTrends($user->company_id);
        
        $expiringArchives = DataArchive::where('company_id', $user->company_id)
            ->expiringSoon(30)
            ->with('archivedBy')
            ->get();
        
        $largestArchives = DataArchive::where('company_id', $user->company_id)
            ->whereNotNull('file_size')
            ->orderBy('file_size', 'desc')
            ->limit(10)
            ->with('archivedBy')
            ->get();

        return view('data-management.reports', compact(
            'company', 'stats', 'archiveByType', 'archiveByStatus', 
            'monthlyTrends', 'expiringArchives', 'largestArchives'
        ));
    }

    private function archiveRecord($companyId, $archiveType, $tableName, $recordId, $retentionPeriod, $reason, $archivedBy)
    {
        // Get the original record
        $record = DB::table($tableName)->where('id', $recordId)->first();
        
        if (!$record) {
            throw new \Exception("Record not found in table {$tableName}");
        }

        // Create archive record
        $archive = DataArchive::create([
            'company_id' => $companyId,
            'archive_type' => $archiveType,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'original_data' => (array) $record,
            'archived_data' => (array) $record,
            'archive_date' => now(),
            'retention_period' => $retentionPeriod,
            'expiry_date' => $retentionPeriod > 0 ? now()->addDays($retentionPeriod) : null,
            'archive_reason' => $reason,
            'archived_by' => $archivedBy
        ]);

        // Create backup file
        $this->createArchiveFile($archive, $record);

        // Soft delete or move the original record
        $this->processOriginalRecord($tableName, $recordId, $archiveType);

        return $archive;
    }

    private function createArchiveFile($archive, $record)
    {
        $fileName = "archive_{$archive->id}_{$archive->archive_type}_{$archive->record_id}.json";
        $filePath = "archives/{$archive->company_id}/{$fileName}";
        
        $fileContent = json_encode([
            'archive_id' => $archive->id,
            'archive_type' => $archive->archive_type,
            'table_name' => $archive->table_name,
            'record_id' => $archive->record_id,
            'archived_data' => $record,
            'archive_date' => $archive->archive_date,
            'metadata' => [
                'created_at' => now(),
                'checksum' => md5(json_encode($record))
            ]
        ], JSON_PRETTY_PRINT);

        Storage::put($filePath, $fileContent);
        
        $archive->update([
            'file_path' => $filePath,
            'file_size' => strlen($fileContent),
            'checksum' => md5($fileContent)
        ]);
    }

    private function processOriginalRecord($tableName, $recordId, $archiveType)
    {
        // For some data types, we might want to keep the record but mark it as archived
        // For others, we might want to soft delete or move to a different table
        
        switch ($archiveType) {
            case DataArchive::TYPE_EMPLOYEE:
                // Mark employee as inactive instead of deleting
                DB::table($tableName)->where('id', $recordId)->update(['is_active' => false]);
                break;
                
            case DataArchive::TYPE_PAYROLL:
            case DataArchive::TYPE_ATTENDANCE:
            case DataArchive::TYPE_LEAVE:
            case DataArchive::TYPE_OVERTIME:
                // These records should be kept for historical purposes
                // Just mark them as archived
                break;
                
            default:
                // For other types, we might want to soft delete
                if (Schema::hasColumn($tableName, 'deleted_at')) {
                    DB::table($tableName)->where('id', $recordId)->update(['deleted_at' => now()]);
                }
                break;
        }
    }

    private function restoreRecord($archive)
    {
        if (!$archive->file_path || !Storage::exists($archive->file_path)) {
            throw new \Exception('Archive file not found');
        }

        $fileContent = Storage::get($archive->file_path);
        $data = json_decode($fileContent, true);

        if (!$data || !isset($data['archived_data'])) {
            throw new \Exception('Invalid archive file format');
        }

        $recordData = $data['archived_data'];
        
        // Remove system fields that shouldn't be restored
        unset($recordData['id'], $recordData['created_at'], $recordData['updated_at'], $recordData['deleted_at']);
        
        // Add restored timestamp
        $recordData['restored_at'] = now();
        $recordData['restored_by'] = auth()->id();

        // Insert the restored record
        $newId = DB::table($archive->table_name)->insertGetId($recordData);

        return $newId;
    }

    private function createBackup($companyId)
    {
        $backupData = [];
        $tables = [
            'employees', 'payrolls', 'attendances', 'leaves', 'overtimes',
            'taxes', 'bpjs', 'benefits', 'employee_benefits', 'performances',
            'compliances', 'compliance_audit_logs', 'data_archives'
        ];

        foreach ($tables as $table) {
            $data = DB::table($table)->where('company_id', $companyId)->get();
            $backupData[$table] = $data;
        }

        $fileName = "backup_{$companyId}_" . now()->format('Y-m-d_H-i-s') . ".json";
        $filePath = "backups/{$companyId}/{$fileName}";
        
        Storage::put($filePath, json_encode($backupData, JSON_PRETTY_PRINT));

        return [
            'file_path' => $filePath,
            'file_size' => Storage::size($filePath),
            'tables_count' => count($tables),
            'total_records' => array_sum(array_map('count', $backupData))
        ];
    }

    private function restoreBackup($file, $companyId)
    {
        $content = file_get_contents($file->getPathname());
        $backupData = json_decode($content, true);

        if (!$backupData) {
            throw new \Exception('Invalid backup file format');
        }

        DB::beginTransaction();
        try {
            foreach ($backupData as $table => $records) {
                foreach ($records as $record) {
                    // Remove system fields
                    unset($record->id, $record->created_at, $record->updated_at);
                    
                    // Ensure company_id is correct
                    $record->company_id = $companyId;
                    
                    DB::table($table)->insert((array) $record);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function performCleanup($companyId)
    {
        $expiredArchives = DataArchive::where('company_id', $companyId)
            ->where('status', DataArchive::STATUS_ACTIVE)
            ->where('expiry_date', '<', now())
            ->get();

        $cleanedCount = 0;

        foreach ($expiredArchives as $archive) {
            try {
                // Delete file if exists
                if ($archive->file_path && Storage::exists($archive->file_path)) {
                    Storage::delete($archive->file_path);
                }

                $archive->markAsExpired();
                $cleanedCount++;
            } catch (\Exception $e) {
                // Log error but continue with other archives
                \Log::error("Failed to cleanup archive {$archive->id}: " . $e->getMessage());
            }
        }

        return $cleanedCount;
    }

    private function getDataManagementStats($companyId)
    {
        $totalArchives = DataArchive::where('company_id', $companyId)->count();
        $activeArchives = DataArchive::where('company_id', $companyId)
            ->where('status', DataArchive::STATUS_ACTIVE)
            ->count();
        $expiredArchives = DataArchive::where('company_id', $companyId)
            ->where('status', DataArchive::STATUS_EXPIRED)
            ->count();
        $totalSize = DataArchive::where('company_id', $companyId)
            ->whereNotNull('file_size')
            ->sum('file_size');

        return [
            'total_archives' => $totalArchives,
            'active_archives' => $activeArchives,
            'expired_archives' => $expiredArchives,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize)
        ];
    }

    private function getArchiveByType($companyId)
    {
        return DataArchive::where('company_id', $companyId)
            ->select('archive_type', DB::raw('count(*) as count'))
            ->groupBy('archive_type')
            ->get()
            ->pluck('count', 'archive_type')
            ->toArray();
    }

    private function getArchiveByStatus($companyId)
    {
        return DataArchive::where('company_id', $companyId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getMonthlyTrends($companyId)
    {
        return DataArchive::where('company_id', $companyId)
            ->where('archive_date', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(archive_date, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 