<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Tax;
use App\Models\Bpjs;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    protected $company;

    public function __construct()
    {
        $this->company = Company::find(Auth::user()->company_id);
    }

    /**
     * Export employee data to Excel
     */
    public function exportEmployees($format = 'xlsx')
    {
        $employees = Employee::where('company_id', $this->company->id)
            ->orderBy('name')
            ->get();

        if ($format === 'xlsx') {
            return $this->exportEmployeesToExcel($employees);
        } else {
            return $this->exportEmployeesToPdf($employees);
        }
    }

    /**
     * Export payroll data to Excel/PDF
     */
    public function exportPayrolls($period = null, $format = 'xlsx')
    {
        $query = Payroll::where('company_id', $this->company->id)
            ->with(['employee']);

        if ($period) {
            $query->where('period', $period);
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'xlsx') {
            return $this->exportPayrollsToExcel($payrolls, $period);
        } else {
            return $this->exportPayrollsToPdf($payrolls, $period);
        }
    }

    /**
     * Export attendance data to Excel/PDF
     */
    public function exportAttendance($startDate = null, $endDate = null, $format = 'xlsx')
    {
        $query = Attendance::where('company_id', $this->company->id)
            ->with(['employee']);

        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        if ($format === 'xlsx') {
            return $this->exportAttendanceToExcel($attendances, $startDate, $endDate);
        } else {
            return $this->exportAttendanceToPdf($attendances, $startDate, $endDate);
        }
    }

    /**
     * Export leave data to Excel/PDF
     */
    public function exportLeaves($startDate = null, $endDate = null, $format = 'xlsx')
    {
        $query = Leave::where('company_id', $this->company->id)
            ->with(['employee']);

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'xlsx') {
            return $this->exportLeavesToExcel($leaves, $startDate, $endDate);
        } else {
            return $this->exportLeavesToPdf($leaves, $startDate, $endDate);
        }
    }

    /**
     * Export tax data to Excel/PDF
     */
    public function exportTaxes($period = null, $format = 'xlsx')
    {
        $query = Tax::where('company_id', $this->company->id)
            ->with(['employee']);

        if ($period) {
            $query->where('tax_period', $period);
        }

        $taxes = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'xlsx') {
            return $this->exportTaxesToExcel($taxes, $period);
        } else {
            return $this->exportTaxesToPdf($taxes, $period);
        }
    }

    /**
     * Export BPJS data to Excel/PDF
     */
    public function exportBpjs($period = null, $format = 'xlsx')
    {
        $query = Bpjs::where('company_id', $this->company->id)
            ->with(['employee']);

        if ($period) {
            $query->where('bpjs_period', $period);
        }

        $bpjs = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'xlsx') {
            return $this->exportBpjsToExcel($bpjs, $period);
        } else {
            return $this->exportBpjsToPdf($bpjs, $period);
        }
    }

    /**
     * Export employees to Excel
     */
    private function exportEmployeesToExcel($employees)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'EMPLOYEE DATA REPORT');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Company info
        $sheet->setCellValue('A2', 'Company: ' . $this->company->name);
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:H3');

        // Headers
        $headers = [
            'A5' => 'Employee ID',
            'B5' => 'Name',
            'C5' => 'Email',
            'D5' => 'Phone',
            'E5' => 'Position',
            'F5' => 'Department',
            'G5' => 'Base Salary',
            'H5' => 'Status',
            'I5' => 'Join Date'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        }

        // Data
        $row = 6;
        foreach ($employees as $employee) {
            $sheet->setCellValue('A' . $row, $employee->employee_id);
            $sheet->setCellValue('B' . $row, $employee->name);
            $sheet->setCellValue('C' . $row, $employee->email);
            $sheet->setCellValue('D' . $row, $employee->phone);
            $sheet->setCellValue('E' . $row, $employee->position);
            $sheet->setCellValue('F' . $row, $employee->department);
            $sheet->setCellValue('G' . $row, number_format($employee->basic_salary, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, ucfirst($employee->status));
            $sheet->setCellValue('I' . $row, $employee->join_date ? $employee->join_date->format('d/m/Y') : '-');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A5:I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'employees_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export payrolls to Excel
     */
    private function exportPayrollsToExcel($payrolls, $period)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $title = 'PAYROLL REPORT';
        if ($period) {
            $title .= ' - ' . $period;
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Company info
        $sheet->setCellValue('A2', 'Company: ' . $this->company->name);
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:J3');

        // Headers
        $headers = [
            'A5' => 'Employee',
            'B5' => 'Period',
            'C5' => 'Base Salary',
            'D5' => 'Overtime',
            'E5' => 'Allowances',
            'F5' => 'Deductions',
            'G5' => 'Tax',
            'H5' => 'BPJS',
            'I5' => 'Net Salary',
            'J5' => 'Status'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        }

        // Data
        $row = 6;
        foreach ($payrolls as $payroll) {
            $sheet->setCellValue('A' . $row, $payroll->employee->name);
            $sheet->setCellValue('B' . $row, $payroll->period);
            $sheet->setCellValue('C' . $row, number_format((float)$payroll->basic_salary, 0, ',', '.'));
            $sheet->setCellValue('D' . $row, number_format((float)($payroll->overtime ?? 0), 0, ',', '.'));
            $sheet->setCellValue('E' . $row, number_format((float)($payroll->allowance ?? 0), 0, ',', '.'));
            $sheet->setCellValue('F' . $row, number_format((float)($payroll->deduction ?? 0), 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format((float)($payroll->tax_amount ?? 0), 0, ',', '.'));
            $sheet->setCellValue('H' . $row, number_format((float)($payroll->bpjs_amount ?? 0), 0, ',', '.'));
            $sheet->setCellValue('I' . $row, number_format((float)$payroll->total_salary, 0, ',', '.'));
            $sheet->setCellValue('J' . $row, ucfirst($payroll->status));
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A5:J' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'payroll_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export attendance to Excel
     */
    private function exportAttendanceToExcel($attendances, $startDate, $endDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $title = 'ATTENDANCE REPORT';
        if ($startDate && $endDate) {
            $title .= ' - ' . $startDate . ' to ' . $endDate;
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Company info
        $sheet->setCellValue('A2', 'Company: ' . $this->company->name);
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:H3');

        // Headers
        $headers = [
            'A5' => 'Employee',
            'B5' => 'Date',
            'C5' => 'Check In',
            'D5' => 'Check Out',
            'E5' => 'Working Hours',
            'F5' => 'Overtime Hours',
            'G5' => 'Status',
            'H5' => 'Notes'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        }

        // Data
        $row = 6;
        foreach ($attendances as $attendance) {
            $sheet->setCellValue('A' . $row, $attendance->employee->name);
            $sheet->setCellValue('B' . $row, $attendance->attendance_date->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-');
            $sheet->setCellValue('D' . $row, $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-');
            $sheet->setCellValue('E' . $row, $attendance->working_hours . ' hours');
            $sheet->setCellValue('F' . $row, $attendance->overtime_hours . ' hours');
            $sheet->setCellValue('G' . $row, ucfirst($attendance->status));
            $sheet->setCellValue('H' . $row, $attendance->notes ?: '-');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A5:H' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export employees to PDF
     */
    private function exportEmployeesToPdf($employees)
    {
        $data = [
            'company' => $this->company,
            'employees' => $employees,
            'generated_at' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.employees-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('employees_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export payrolls to PDF
     */
    private function exportPayrollsToPdf($payrolls, $period)
    {
        $data = [
            'company' => $this->company,
            'payrolls' => $payrolls,
            'period' => $period,
            'generated_at' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.payrolls-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'payroll_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export attendance to PDF
     */
    private function exportAttendanceToPdf($attendances, $startDate, $endDate)
    {
        $data = [
            'company' => $this->company,
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generated_at' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.attendance-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('attendance_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export taxes to Excel
     */
    private function exportTaxesToExcel($taxes, $period)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $title = 'TAX REPORT';
        if ($period) {
            $title .= ' - ' . $period;
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Company info
        $sheet->setCellValue('A2', 'Company: ' . $this->company->name);
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:I3');

        // Headers
        $headers = [
            'A5' => 'Employee',
            'B5' => 'Period',
            'C5' => 'Taxable Income',
            'D5' => 'PTKP Status',
            'E5' => 'PTKP Amount',
            'F5' => 'Taxable Base',
            'G5' => 'Tax Rate',
            'H5' => 'Tax Amount',
            'I5' => 'Status'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        }

        // Data
        $row = 6;
        foreach ($taxes as $tax) {
            $sheet->setCellValue('A' . $row, $tax->employee->name);
            $sheet->setCellValue('B' . $row, $tax->tax_period);
            $sheet->setCellValue('C' . $row, number_format($tax->taxable_income, 0, ',', '.'));
            $sheet->setCellValue('D' . $row, $tax->ptkp_status);
            $sheet->setCellValue('E' . $row, number_format($tax->ptkp_amount, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, number_format($tax->taxable_base, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, $tax->tax_rate . '%');
            $sheet->setCellValue('H' . $row, number_format($tax->tax_amount, 0, ',', '.'));
            $sheet->setCellValue('I' . $row, ucfirst($tax->status));
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A5:I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'taxes_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export BPJS to Excel
     */
    private function exportBpjsToExcel($bpjs, $period)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $title = 'BPJS REPORT';
        if ($period) {
            $title .= ' - ' . $period;
        }
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Company info
        $sheet->setCellValue('A2', 'Company: ' . $this->company->name);
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A3:I3');

        // Headers
        $headers = [
            'A5' => 'Employee',
            'B5' => 'Period',
            'C5' => 'BPJS Type',
            'D5' => 'Base Salary',
            'E5' => 'Employee Contribution',
            'F5' => 'Company Contribution',
            'G5' => 'Total Contribution',
            'H5' => 'Status',
            'I5' => 'Payment Date'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
        }

        // Data
        $row = 6;
        foreach ($bpjs as $bpjsRecord) {
            $sheet->setCellValue('A' . $row, $bpjsRecord->employee->name);
            $sheet->setCellValue('B' . $row, $bpjsRecord->bpjs_period);
            $sheet->setCellValue('C' . $row, ucfirst($bpjsRecord->bpjs_type));
            $sheet->setCellValue('D' . $row, number_format($bpjsRecord->base_salary, 0, ',', '.'));
            $sheet->setCellValue('E' . $row, number_format($bpjsRecord->employee_contribution, 0, ',', '.'));
            $sheet->setCellValue('F' . $row, number_format($bpjsRecord->company_contribution, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, number_format($bpjsRecord->total_contribution, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, ucfirst($bpjsRecord->status));
            $sheet->setCellValue('I' . $row, $bpjsRecord->payment_date ? $bpjsRecord->payment_date->format('d/m/Y') : '-');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A5:I' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = 'bpjs_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export taxes to PDF
     */
    private function exportTaxesToPdf($taxes, $period)
    {
        $data = [
            'company' => $this->company,
            'taxes' => $taxes,
            'period' => $period,
            'generated_at' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.taxes-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'taxes_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export BPJS to PDF
     */
    private function exportBpjsToPdf($bpjs, $period)
    {
        $data = [
            'company' => $this->company,
            'bpjs' => $bpjs,
            'period' => $period,
            'generated_at' => now()->format('d/m/Y H:i')
        ];

        $pdf = Pdf::loadView('exports.bpjs-pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'bpjs_' . ($period ? $period . '_' : '') . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }
} 