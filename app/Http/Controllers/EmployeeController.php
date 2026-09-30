<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(employee_code) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(instagram_username) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('active_status')) {
            $status = $request->input('active_status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department', 'active_status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();
        
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function restore($id)
    {
        $employee = Employee::withTrashed()->findOrFail($id);

        if (Employee::where('employee_code', $employee->employee_code)->where('id', '!=', $employee->id)->exists()) {
            throw ValidationException::withMessages(['employee_code' => 'The employee code has already been taken by another employee.']);
        }

        if ($employee->instagram_user_id && Employee::where('instagram_user_id', $employee->instagram_user_id)->where('id', '!=', $employee->id)->exists()) {
            throw ValidationException::withMessages(['instagram_user_id' => 'The instagram user id has already been taken by another employee.']);
        }

        $employee->restore();

        return redirect()->route('employees.index')->with('success', 'Employee restored successfully.');
    }
}
