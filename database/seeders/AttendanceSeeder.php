<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        
        if ($employees->isEmpty()) {
            $this->command->info('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $this->command->info('Creating sample attendance data...');

        // Generate attendance for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $employee) {
                // Random attendance status
                $status = $this->getRandomStatus();
                
                $checkIn = null;
                $checkOut = null;
                $totalHours = 0;
                $overtimeHours = 0;

                if ($status === 'present' || $status === 'late') {
                    // Generate check-in time (between 7:00 and 9:30)
                    $checkInHour = rand(7, 9);
                    $checkInMinute = rand(0, 59);
                    $checkIn = Carbon::parse($date)->setTime($checkInHour, $checkInMinute);

                    // Generate check-out time (between 16:00 and 19:00)
                    $checkOutHour = rand(16, 19);
                    $checkOutMinute = rand(0, 59);
                    $checkOut = Carbon::parse($date)->setTime($checkOutHour, $checkOutMinute);

                    // Calculate total hours
                    $totalHours = $checkIn->diffInHours($checkOut, true);
                    
                    // Calculate overtime (more than 8 hours)
                    $overtimeHours = max(0, $totalHours - 8);
                }

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn ? $checkIn->format('H:i:s') : null,
                    'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                    'total_hours' => $totalHours,
                    'overtime_hours' => $overtimeHours,
                    'status' => $status,
                    'notes' => $this->getRandomNotes($status),
                    'check_in_location' => $status !== 'absent' ? '-6.2088,106.8456' : null,
                    'check_out_location' => $status !== 'absent' ? '-6.2088,106.8456' : null,
                    'check_in_ip' => $status !== 'absent' ? '192.168.1.' . rand(1, 255) : null,
                    'check_out_ip' => $status !== 'absent' ? '192.168.1.' . rand(1, 255) : null,
                    'check_in_device' => $status !== 'absent' ? 'Chrome/Windows' : null,
                    'check_out_device' => $status !== 'absent' ? 'Chrome/Windows' : null,
                ]);
            }
        }

        $this->command->info('Sample attendance data created successfully!');
    }

    private function getRandomStatus()
    {
        $statuses = ['present', 'present', 'present', 'present', 'present', 'late', 'absent', 'half_day'];
        return $statuses[array_rand($statuses)];
    }

    private function getRandomNotes($status)
    {
        $notes = [
            'present' => ['Hadir tepat waktu', 'Hadir seperti biasa', ''],
            'late' => ['Terlambat karena macet', 'Terlambat karena hujan', 'Terlambat karena transportasi'],
            'absent' => ['Sakit', 'Izin keluarga', 'Cuti'],
            'half_day' => ['Setengah hari karena urusan keluarga', 'Setengah hari karena meeting'],
        ];

        if (isset($notes[$status])) {
            $statusNotes = $notes[$status];
            return $statusNotes[array_rand($statusNotes)];
        }

        return '';
    }
}
