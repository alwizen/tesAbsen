<?php
// tests/Feature/AttendanceTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_check_in(): void
    {
        $department = Department::factory()->create();

        WorkSchedule::create([
            'department_id' => $department->id,
            'check_in_time' => '08:00:00',
            'check_out_time' => '16:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'rfid_number' => 'TEST001',
        ]);

        $response = $this->postJson('/api/attendance/tap', [
            'rfid_number' => 'TEST001',
            'tapped_at' => now()->setTime(8, 10)->toDateTimeString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'employee_id',
                    'work_date',
                    'check_in_at',
                    'late_minutes',
                ],
            ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'late_minutes' => 0, // Dalam grace period
        ]);
    }

    public function test_overnight_shift_work_date_calculation(): void
    {
        $department = Department::factory()->create();

        WorkSchedule::create([
            'department_id' => $department->id,
            'check_in_time' => '22:00:00',
            'check_out_time' => '06:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => true,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'rfid_number' => 'TEST002',
        ]);

        // Check-in pada 31 Jan jam 22:00
        $checkInTime = now()->setDate(2025, 1, 31)->setTime(22, 0);

        $response = $this->postJson('/api/attendance/tap', [
            'rfid_number' => 'TEST002',
            'tapped_at' => $checkInTime->toDateTimeString(),
        ]);

        $response->assertStatus(200);

        // Work date harus 31 Jan, bukan 1 Feb
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'work_date' => '2025-01-31 00:00:00',
        ]);
    }

    public function test_employee_can_check_out_after_check_in(): void
    {
        $department = Department::factory()->create();

        WorkSchedule::create([
            'department_id' => $department->id,
            'check_in_time' => '08:00:00',
            'check_out_time' => '16:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'rfid_number' => 'TEST003',
        ]);

        // Tap pertama (check-in)
        $this->postJson('/api/attendance/tap', [
            'rfid_number' => 'TEST003',
            'tapped_at' => '2025-02-01 08:00:00',
        ])->assertStatus(200);

        // Tap kedua (check-out)
        $this->postJson('/api/attendance/tap', [
            'rfid_number' => 'TEST003',
            'tapped_at' => '2025-02-01 16:00:00',
        ])->assertStatus(200);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'present',
        ]);

        $attendance = \App\Models\Attendance::first();

        $this->assertEquals(
            '2025-02-01',
            $attendance->work_date->toDateString()
        );

        $this->assertNotNull($attendance->check_out_at);
        $this->assertGreaterThan(0, $attendance->work_hours);
    }
}
