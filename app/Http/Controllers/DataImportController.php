<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DataImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function importEmployees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'import_type' => 'required|in:create,update,upsert',
            'skip_header' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $importType = $request->input('import_type');
            $skipHeader = $request->boolean('skip_header', true);

            // Store file temporarily
            $filename = 'import_' . time() . '.' . $file->getClientOriginalExtension();
            $filepath = $file->storeAs('temp/imports', $filename);

            // Read Excel file
            $spreadsheet = IOFactory::load(storage_path('app/' . $filepath));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header if needed
            if ($skipHeader) {
                array_shift($rows);
            }

            $results = [
                'total' => count($rows),
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                try {
                    $employeeData = $this->mapEmployeeData($row);
                    
                    if ($this->validateEmployeeData($employeeData)) {
                        switch ($importType) {
                            case 'create':
                                $this->createEmployee($employeeData);
                                break;
                            case 'update':
                                $this->updateEmployee($employeeData);
                                break;
                            case 'upsert':
                                $this->upsertEmployee($employeeData);
                                break;
                        }
                        $results['success']++;
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Row " . ($index + 1) . ": Invalid data format";
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            // Clean up temporary file
            Storage::delete($filepath);

            return redirect()->back()
                ->with('success', "Import completed. {$results['success']} records processed successfully, {$results['failed']} failed.")
                ->with('import_results', $results);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($filepath)) {
                Storage::delete($filepath);
            }

            return redirect()->back()
                ->withErrors(['file' => 'Import failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function downloadTemplate($type)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        switch ($type) {
            case 'employees':
                $this->createEmployeeTemplate($worksheet);
                break;
            case 'payroll':
                $this->createPayrollTemplate($worksheet);
                break;
            case 'attendance':
                $this->createAttendanceTemplate($worksheet);
                break;
            default:
                abort(404);
        }

        $filename = $type . '_import_template.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function validateImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'skip_header' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $file = $request->file('file');
            $skipHeader = $request->boolean('skip_header', true);

            // Store file temporarily
            $filename = 'validate_' . time() . '.' . $file->getClientOriginalExtension();
            $filepath = $file->storeAs('temp/imports', $filename);

            // Read Excel file
            $spreadsheet = IOFactory::load(storage_path('app/' . $filepath));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header if needed
            if ($skipHeader) {
                array_shift($rows);
            }

            $validationResults = [
                'total_rows' => count($rows),
                'valid_rows' => 0,
                'invalid_rows' => 0,
                'errors' => []
            ];

            foreach ($rows as $index => $row) {
                $employeeData = $this->mapEmployeeData($row);
                
                if ($this->validateEmployeeData($employeeData)) {
                    $validationResults['valid_rows']++;
                } else {
                    $validationResults['invalid_rows']++;
                    $validationResults['errors'][] = "Row " . ($index + 1) . ": Invalid data format";
                }
            }

            // Clean up temporary file
            Storage::delete($filepath);

            return response()->json([
                'success' => true,
                'results' => $validationResults
            ]);

        } catch (\Exception $e) {
            if (isset($filepath)) {
                Storage::delete($filepath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ]);
        }
    }

    private function mapEmployeeData($row)
    {
        return [
            'employee_id' => $row[0] ?? null,
            'first_name' => $row[1] ?? null,
            'last_name' => $row[2] ?? null,
            'email' => $row[3] ?? null,
            'phone' => $row[4] ?? null,
            'position' => $row[5] ?? null,
            'department' => $row[6] ?? null,
            'hire_date' => $row[7] ?? null,
            'salary' => $row[8] ?? null,
            'status' => $row[9] ?? 'active'
        ];
    }

    private function validateEmployeeData($data)
    {
        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'status' => 'in:active,inactive,terminated'
        ]);

        return !$validator->fails();
    }

    private function createEmployee($data)
    {
        Employee::create([
            'company_id' => Auth::user()->company_id,
            'employee_id' => $data['employee_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'position' => $data['position'],
            'department' => $data['department'],
            'hire_date' => $data['hire_date'],
            'salary' => $data['salary'],
            'status' => $data['status']
        ]);
    }

    private function updateEmployee($data)
    {
        $employee = Employee::where('company_id', Auth::user()->company_id)
            ->where('employee_id', $data['employee_id'])
            ->first();

        if ($employee) {
            $employee->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'position' => $data['position'],
                'department' => $data['department'],
                'hire_date' => $data['hire_date'],
                'salary' => $data['salary'],
                'status' => $data['status']
            ]);
        }
    }

    private function upsertEmployee($data)
    {
        Employee::updateOrCreate(
            [
                'company_id' => Auth::user()->company_id,
                'employee_id' => $data['employee_id']
            ],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'position' => $data['position'],
                'department' => $data['department'],
                'hire_date' => $data['hire_date'],
                'salary' => $data['salary'],
                'status' => $data['status']
            ]
        );
    }

    private function createEmployeeTemplate($worksheet)
    {
        // Set headers
        $headers = [
            'Employee ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Position',
            'Department',
            'Hire Date',
            'Salary',
            'Status'
        ];

        foreach ($headers as $col => $header) {
            $worksheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Add sample data
        $sampleData = [
            ['EMP001', 'John', 'Doe', 'john.doe@company.com', '08123456789', 'Software Engineer', 'IT', '2024-01-15', '8000000', 'active'],
            ['EMP002', 'Jane', 'Smith', 'jane.smith@company.com', '08123456790', 'HR Manager', 'HR', '2024-02-01', '12000000', 'active'],
        ];

        foreach ($sampleData as $row => $data) {
            foreach ($data as $col => $value) {
                $worksheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value);
            }
        }

        // Style headers
        $worksheet->getStyle('A1:J1')->getFont()->setBold(true);
    }

    private function createPayrollTemplate($worksheet)
    {
        // Set headers for payroll template
        $headers = [
            'Employee ID',
            'Month',
            'Year',
            'Basic Salary',
            'Allowances',
            'Deductions',
            'Overtime Hours',
            'Overtime Rate',
            'Leave Days',
            'Notes'
        ];

        foreach ($headers as $col => $header) {
            $worksheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Style headers
        $worksheet->getStyle('A1:J1')->getFont()->setBold(true);
    }

    private function createAttendanceTemplate($worksheet)
    {
        // Set headers for attendance template
        $headers = [
            'Employee ID',
            'Date',
            'Check In',
            'Check Out',
            'Status',
            'Notes'
        ];

        foreach ($headers as $col => $header) {
            $worksheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Style headers
        $worksheet->getStyle('A1:F1')->getFont()->setBold(true);
    }
} 