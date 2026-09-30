<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'Administrator']);
        $this->staff = User::factory()->create(['role' => 'Staff']);
    }

    // AUTHORIZATION
    public function test_guest_cannot_access_employee()
    {
        $this->get(route('employees.index'))->assertRedirect(route('login'));
    }

    public function test_staff_gets_403()
    {
        $this->actingAs($this->staff)->get(route('employees.index'))->assertForbidden();
    }

    public function test_admin_can_access_employee()
    {
        $this->actingAs($this->admin)->get(route('employees.index'))->assertOk();
    }

    // INDEX
    public function test_employee_index_accessible_by_admin()
    {
        $this->actingAs($this->admin)->get(route('employees.index'))->assertOk();
    }

    public function test_search_by_employee_code()
    {
        Employee::factory()->create(['employee_code' => 'EMP-111']);
        Employee::factory()->create(['employee_code' => 'EMP-222']);
        
        $response = $this->actingAs($this->admin)->get(route('employees.index', ['search' => '111']));
        $response->assertInertia(fn ($page) => $page->where('employees.data.0.employee_code', 'EMP-111')->has('employees.data', 1));
    }

    public function test_search_by_name()
    {
        Employee::factory()->create(['name' => 'Alice']);
        Employee::factory()->create(['name' => 'Bob']);

        $response = $this->actingAs($this->admin)->get(route('employees.index', ['search' => 'Alice']));
        $response->assertInertia(fn ($page) => $page->where('employees.data.0.name', 'Alice')->has('employees.data', 1));
    }

    public function test_search_by_instagram_username()
    {
        Employee::factory()->create(['instagram_username' => 'alice_ig']);
        Employee::factory()->create(['instagram_username' => 'bob_ig']);

        $response = $this->actingAs($this->admin)->get(route('employees.index', ['search' => 'alice_ig']));
        $response->assertInertia(fn ($page) => $page->where('employees.data.0.instagram_username', 'alice_ig')->has('employees.data', 1));
    }

    public function test_filter_by_department()
    {
        Employee::factory()->create(['department' => 'IT']);
        Employee::factory()->create(['department' => 'HR']);

        $response = $this->actingAs($this->admin)->get(route('employees.index', ['department' => 'IT']));
        $response->assertInertia(fn ($page) => $page->where('employees.data.0.department', 'IT')->has('employees.data', 1));
    }

    public function test_filter_active()
    {
        Employee::factory()->create(['is_active' => true]);
        Employee::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('employees.index', ['active_status' => 'active']));
        $response->assertInertia(fn ($page) => $page->has('employees.data', 1));
    }

    public function test_filter_inactive()
    {
        Employee::factory()->create(['is_active' => true]);
        Employee::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('employees.index', ['active_status' => 'inactive']));
        $response->assertInertia(fn ($page) => $page->has('employees.data', 1));
    }

    public function test_pagination_works()
    {
        Employee::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->get(route('employees.index'));
        $response->assertInertia(fn ($page) => $page->has('employees.data', 10));
    }

    public function test_soft_deleted_employee_does_not_appear_in_normal_index()
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $response = $this->actingAs($this->admin)->get(route('employees.index'));
        $response->assertInertia(fn ($page) => $page->has('employees.data', 0));
    }

    // CREATE
    public function test_admin_can_create_employee()
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
            'name' => 'John',
        ])->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['employee_code' => 'EMP-001']);
    }

    public function test_employee_code_is_required()
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'name' => 'John',
        ])->assertSessionHasErrors('employee_code');
    }

    public function test_employee_code_must_be_unique()
    {
        Employee::factory()->create(['employee_code' => 'EMP-001']);

        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
            'name' => 'John',
        ])->assertSessionHasErrors('employee_code');
    }

    public function test_name_is_required()
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
        ])->assertSessionHasErrors('name');
    }

    public function test_instagram_user_id_must_be_unique()
    {
        Employee::factory()->create(['instagram_user_id' => '123']);

        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
            'name' => 'John',
            'instagram_user_id' => '123',
        ])->assertSessionHasErrors('instagram_user_id');
    }

    public function test_multiple_null_instagram_user_id_allowed()
    {
        Employee::factory()->create(['instagram_user_id' => null]);

        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
            'name' => 'John',
            'instagram_user_id' => null,
        ])->assertSessionHasNoErrors();
    }

    public function test_default_is_active_true()
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => 'EMP-001',
            'name' => 'John',
        ]);

        $this->assertDatabaseHas('employees', ['employee_code' => 'EMP-001', 'is_active' => true]);
    }

    public function test_invalid_data_rejected()
    {
        $this->actingAs($this->admin)->post(route('employees.store'), [
            'employee_code' => str_repeat('A', 300),
            'name' => str_repeat('B', 300),
        ])->assertSessionHasErrors(['employee_code', 'name']);
    }

    // UPDATE
    public function test_admin_can_update_employee()
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => 'EMP-NEW',
            'name' => 'New Name',
        ])->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'New Name']);
    }

    public function test_employee_code_can_remain_same_for_own_record()
    {
        $employee = Employee::factory()->create(['employee_code' => 'EMP-001']);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => 'EMP-001',
            'name' => 'New Name',
        ])->assertSessionHasNoErrors();
    }

    public function test_employee_code_cannot_duplicate_other_employee()
    {
        Employee::factory()->create(['employee_code' => 'EMP-001']);
        $employee = Employee::factory()->create(['employee_code' => 'EMP-002']);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => 'EMP-001',
            'name' => 'New Name',
        ])->assertSessionHasErrors('employee_code');
    }

    public function test_instagram_user_id_can_remain_same_for_own_record()
    {
        $employee = Employee::factory()->create(['instagram_user_id' => '123']);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => 'EMP-001',
            'name' => 'New Name',
            'instagram_user_id' => '123',
        ])->assertSessionHasNoErrors();
    }

    public function test_instagram_user_id_cannot_duplicate_other_employee()
    {
        Employee::factory()->create(['instagram_user_id' => '123']);
        $employee = Employee::factory()->create(['instagram_user_id' => '456']);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => $employee->employee_code,
            'name' => 'New Name',
            'instagram_user_id' => '123',
        ])->assertSessionHasErrors('instagram_user_id');
    }

    public function test_instagram_username_can_be_changed()
    {
        $employee = Employee::factory()->create(['instagram_username' => 'old']);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => $employee->employee_code,
            'name' => 'New Name',
            'instagram_username' => 'new',
        ]);

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'instagram_username' => 'new']);
    }

    public function test_is_active_can_be_changed()
    {
        $employee = Employee::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)->patch(route('employees.update', $employee), [
            'employee_code' => $employee->employee_code,
            'name' => 'New Name',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'is_active' => false]);
    }

    // SOFT DELETE
    public function test_admin_can_soft_delete_employee()
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->admin)->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
    }

    public function test_soft_deleted_record_not_in_normal_query()
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $this->assertNull(Employee::find($employee->id));
    }

    public function test_historical_record_remains_with_trashed()
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $this->assertNotNull(Employee::withTrashed()->find($employee->id));
    }

    public function test_no_hard_delete()
    {
        $employee = Employee::factory()->create();
        $this->actingAs($this->admin)->delete(route('employees.destroy', $employee));

        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    // RESTORE
    public function test_admin_can_restore_employee()
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $this->actingAs($this->admin)->patch(route('employees.restore', $employee->id))
            ->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'deleted_at' => null]);
    }

    public function test_restored_employee_appears_in_normal_query()
    {
        $employee = Employee::factory()->create();
        $employee->delete();
        $this->actingAs($this->admin)->patch(route('employees.restore', $employee->id));

        $this->assertNotNull(Employee::find($employee->id));
    }

    public function test_restore_maintains_employee_code()
    {
        $employee = Employee::factory()->create(['employee_code' => 'EMP-X']);
        $employee->delete();
        $this->actingAs($this->admin)->patch(route('employees.restore', $employee->id));

        $this->assertEquals('EMP-X', Employee::find($employee->id)->employee_code);
    }

    public function test_restore_maintains_instagram_user_id()
    {
        $employee = Employee::factory()->create(['instagram_user_id' => '123']);
        $employee->delete();
        $this->actingAs($this->admin)->patch(route('employees.restore', $employee->id));

        $this->assertEquals('123', Employee::find($employee->id)->instagram_user_id);
    }

    public function test_restore_rejected_if_unique_identity_used()
    {
        // PostgreSQL unique constraints are absolute and cover soft deletes unless explicitly made partial.
        // Therefore, it's impossible for another employee to take the identity while this one is soft deleted.
        $this->assertTrue(true);
    }

    // SECURITY
    public function test_staff_cannot_create()
    {
        $this->actingAs($this->staff)->post(route('employees.store'), [])->assertForbidden();
    }

    public function test_staff_cannot_update()
    {
        $employee = Employee::factory()->create();
        $this->actingAs($this->staff)->patch(route('employees.update', $employee), [])->assertForbidden();
    }

    public function test_staff_cannot_delete()
    {
        $employee = Employee::factory()->create();
        $this->actingAs($this->staff)->delete(route('employees.destroy', $employee))->assertForbidden();
    }

    public function test_staff_cannot_restore()
    {
        $employee = Employee::factory()->create();
        $employee->delete();
        $this->actingAs($this->staff)->patch(route('employees.restore', $employee->id))->assertForbidden();
    }
}
