<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_employee()
    {
        $employee = Employee::factory()->create([
            'employee_code' => 'EMP-001',
            'name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP-001',
            'name' => 'John Doe',
        ]);
    }

    public function test_employee_code_must_be_unique()
    {
        Employee::factory()->create(['employee_code' => 'EMP-001']);

        $this->expectException(QueryException::class);
        Employee::factory()->create(['employee_code' => 'EMP-001']);
    }

    public function test_instagram_user_id_must_be_unique()
    {
        Employee::factory()->create(['instagram_user_id' => '1234567890']);

        $this->expectException(QueryException::class);
        Employee::factory()->create(['instagram_user_id' => '1234567890']);
    }

    public function test_instagram_user_id_can_be_null_for_multiple_employees()
    {
        Employee::factory()->create(['instagram_user_id' => null]);
        Employee::factory()->create(['instagram_user_id' => null]);

        $this->assertEquals(2, Employee::count());
    }

    public function test_can_soft_delete_employee()
    {
        $employee = Employee::factory()->create();

        $employee->delete();

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_default_is_active_is_true()
    {
        $employee = Employee::create([
            'employee_code' => 'EMP-TEST',
            'name' => 'Active Test',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'is_active' => true,
        ]);

        $freshEmployee = Employee::find($employee->id);
        $this->assertTrue($freshEmployee->is_active);
        $this->assertIsBool($freshEmployee->is_active);
    }

    public function test_restore_after_soft_delete()
    {
        $employee = Employee::factory()->create();
        $employeeId = $employee->id;

        $employee->delete();
        $this->assertNull(Employee::find($employeeId));

        $employee->restore();
        $this->assertNotNull(Employee::find($employeeId));
        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'deleted_at' => null,
        ]);
    }

    public function test_mutable_instagram_username()
    {
        $employee = Employee::factory()->create([
            'instagram_user_id' => '111',
            'instagram_username' => 'old_name',
        ]);

        $employee->instagram_username = 'new_name';
        $employee->save();

        $freshEmployee = Employee::find($employee->id);
        $this->assertEquals('new_name', $freshEmployee->instagram_username);
        $this->assertEquals('111', $freshEmployee->instagram_user_id);
    }

    public function test_historical_row_after_soft_delete()
    {
        $employee = Employee::factory()->create([
            'instagram_user_id' => '222',
        ]);

        $employee->delete();

        $this->assertNull(Employee::find($employee->id));
        
        $historical = Employee::withTrashed()->find($employee->id);
        $this->assertNotNull($historical);
        $this->assertEquals('222', $historical->instagram_user_id);
    }

    public function test_model_fillable_and_cast_behavior()
    {
        $employee = new Employee();
        $employee->fill([
            'employee_code' => 'EMP-FILL',
            'name' => 'Fillable Test',
            'is_active' => '1',
        ]);

        $this->assertEquals('EMP-FILL', $employee->employee_code);
        $this->assertTrue($employee->is_active);
    }
}
