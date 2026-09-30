import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';
import { PageProps, PaginatedData, Employee, PaginationLink } from '../../types';

interface Filters {
    search?: string;
    department?: string;
    active_status?: string;
}

interface EmployeesIndexProps extends PageProps {
    employees: PaginatedData<Employee>;
    filters: Filters;
}

export default function Index({ employees, filters }: EmployeesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [department, setDepartment] = useState(filters.department || '');
    const [activeStatus, setActiveStatus] = useState(filters.active_status || '');

    // Delay searching while typing
    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            fetchEmployees();
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [search]);

    // Fetch immediately for dropdowns
    useEffect(() => {
        if (department !== (filters.department || '') || activeStatus !== (filters.active_status || '')) {
            fetchEmployees();
        }
    }, [department, activeStatus]);

    const fetchEmployees = () => {
        router.get('/employees', {
            search,
            department,
            active_status: activeStatus,
        }, { preserveState: true, replace: true });
    };

    const deleteEmployee = (id: number) => {
        if (confirm('Apakah Anda yakin ingin menonaktifkan/menghapus secara soft delete pegawai ini?')) {
            router.delete(`/employees/${id}`, {
                preserveScroll: true,
            });
        }
    };

    const resetFilters = () => {
        setSearch('');
        setDepartment('');
        setActiveStatus('');
    };

    return (
        <AuthenticatedLayout>
            <Head title="Employee Management" />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {/* Header Section */}
                <div className="md:flex md:items-center md:justify-between mb-6">
                    <div className="flex-1 min-w-0">
                        <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            Employee Management
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Kelola data pegawai yang menjadi objek monitoring Instagram.
                        </p>
                    </div>
                    <div className="mt-4 flex md:mt-0 md:ml-4">
                        <Link
                            href="/employees/create"
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Tambah Employee
                        </Link>
                    </div>
                </div>

                {/* Filters Section */}
                <div className="bg-white p-4 shadow rounded-lg mb-6 flex flex-col md:flex-row gap-4 items-end">
                    <div className="flex-1 w-full">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <input
                            type="text"
                            placeholder="Cari kode, nama, atau username IG..."
                            className="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                    <div className="w-full md:w-48">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select
                            className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            value={department}
                            onChange={(e) => setDepartment(e.target.value)}
                        >
                            <option value="">Semua Department</option>
                            <option value="IT">IT</option>
                            <option value="Marketing">Marketing</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                        </select>
                    </div>
                    <div className="w-full md:w-48">
                        <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            className="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            value={activeStatus}
                            onChange={(e) => setActiveStatus(e.target.value)}
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                    <div>
                        <button
                            onClick={resetFilters}
                            className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>

                {/* Table Section */}
                <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instagram
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instagram User ID
                                    </th>
                                    <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" className="relative px-6 py-3">
                                        <span className="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {employees.data.length > 0 ? (
                                    employees.data.map((employee) => (
                                        <tr key={employee.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center">
                                                    <div className="ml-4">
                                                        <div className="text-sm font-medium text-gray-900">{employee.name}</div>
                                                        <div className="text-sm text-gray-500">{employee.employee_code}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">{employee.department || '-'}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {employee.instagram_username ? `@${employee.instagram_username}` : '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {employee.instagram_user_id || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                    employee.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                }`}>
                                                    {employee.is_active ? 'Aktif' : 'Tidak Aktif'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                <Link href={`/employees/${employee.id}/edit`} className="text-blue-600 hover:text-blue-900">
                                                    Edit
                                                </Link>
                                                <button onClick={() => deleteEmployee(employee.id)} className="text-red-600 hover:text-red-900">
                                                    Nonaktifkan
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-4 text-center text-gray-500">
                                            {(search || department || activeStatus) ? 'Data Employee tidak ditemukan.' : 'Belum ada data Employee.'}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Pagination */}
                    {employees.links.length > 3 && (
                        <div className="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p className="text-sm text-gray-700">
                                        Showing <span className="font-medium">{employees.from || 0}</span> to <span className="font-medium">{employees.to || 0}</span> of{' '}
                                        <span className="font-medium">{employees.total}</span> results
                                    </p>
                                </div>
                                <div>
                                    <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        {employees.links.map((link: PaginationLink, i: number) => {
                                            const isActive = link.active;
                                            const isClickable = link.url !== null;
                                            return (
                                                <Link
                                                    key={i}
                                                    href={isClickable ? link.url! : '#'}
                                                    className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                                        isActive 
                                                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                                            : isClickable 
                                                                ? 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                                : 'bg-white border-gray-300 text-gray-300 cursor-not-allowed'
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                    preserveState={true}
                                                    preserveScroll={true}
                                                />
                                            );
                                        })}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
