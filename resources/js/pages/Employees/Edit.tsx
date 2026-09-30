import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';
import { PageProps, Employee } from '../../types';

interface EditProps extends PageProps {
    employee: Employee;
}

export default function Edit({ employee }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        employee_code: employee.employee_code || '',
        name: employee.name || '',
        department: employee.department || '',
        instagram_user_id: employee.instagram_user_id || '',
        instagram_username: employee.instagram_username || '',
        is_active: employee.is_active,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/employees/${employee.id}`);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Edit Employee" />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div className="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 className="text-lg leading-6 font-medium text-gray-900">Edit Employee</h3>
                            <p className="mt-1 max-w-2xl text-sm text-gray-500">
                                Perbarui data pegawai yang menjadi objek monitoring Instagram.
                            </p>
                        </div>
                    </div>
                    <div className="px-4 py-5 sm:p-6">
                        <form onSubmit={submit} className="space-y-8 max-w-4xl">
                            {/* Informasi Dasar Section */}
                            <section>
                                <h4 className="text-base font-medium text-gray-900 mb-4 border-b pb-2">Informasi Dasar</h4>
                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    {/* Employee Code */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Employee Code <span className="text-red-500">*</span></label>
                                        <input
                                            type="text"
                                            value={data.employee_code}
                                            onChange={(e) => setData('employee_code', e.target.value)}
                                            className={`block w-full rounded-md shadow-sm sm:text-sm ${
                                                errors.employee_code ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                            }`}
                                            required
                                        />
                                        {errors.employee_code && <p className="text-sm text-red-600">{errors.employee_code}</p>}
                                    </div>

                                    {/* Name */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Nama Lengkap <span className="text-red-500">*</span></label>
                                        <input
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className={`block w-full rounded-md shadow-sm sm:text-sm ${
                                                errors.name ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                            }`}
                                            required
                                        />
                                        {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                    </div>

                                    {/* Department */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Department</label>
                                        <input
                                            type="text"
                                            value={data.department}
                                            onChange={(e) => setData('department', e.target.value)}
                                            className={`block w-full rounded-md shadow-sm sm:text-sm ${
                                                errors.department ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                            }`}
                                        />
                                        {errors.department && <p className="text-sm text-red-600">{errors.department}</p>}
                                    </div>

                                    {/* Status */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Status <span className="text-red-500">*</span></label>
                                        <select
                                            value={data.is_active ? 'true' : 'false'}
                                            onChange={(e) => setData('is_active', e.target.value === 'true')}
                                            className={`block w-full rounded-md shadow-sm sm:text-sm ${
                                                errors.is_active ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                            }`}
                                        >
                                            <option value="true">Aktif</option>
                                            <option value="false">Tidak Aktif</option>
                                        </select>
                                        {errors.is_active && <p className="text-sm text-red-600">{errors.is_active}</p>}
                                    </div>
                                </div>
                            </section>

                            {/* Instagram Section */}
                            <section className="pt-2">
                                <h4 className="text-base font-medium text-gray-900 mb-4 border-b pb-2">Informasi Instagram</h4>
                                <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    {/* Instagram User ID */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Instagram User ID</label>
                                        <input
                                            type="text"
                                            value={data.instagram_user_id}
                                            onChange={(e) => setData('instagram_user_id', e.target.value)}
                                            className={`block w-full rounded-md shadow-sm sm:text-sm ${
                                                errors.instagram_user_id ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                            }`}
                                        />
                                        <p className="text-xs text-gray-500">ID Instagram digunakan sebagai identitas utama akun. Username dapat berubah.</p>
                                        {errors.instagram_user_id && <p className="text-sm text-red-600">{errors.instagram_user_id}</p>}
                                    </div>

                                    {/* Instagram Username */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">Instagram Username</label>
                                        <div className="flex rounded-md shadow-sm">
                                            <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                                @
                                            </span>
                                            <input
                                                type="text"
                                                value={data.instagram_username}
                                                onChange={(e) => setData('instagram_username', e.target.value)}
                                                className={`flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md sm:text-sm ${
                                                    errors.instagram_username ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                                }`}
                                            />
                                        </div>
                                        <p className="text-xs text-gray-500">Username dapat berubah dan bukan identitas utama akun.</p>
                                        {errors.instagram_username && <p className="text-sm text-red-600">{errors.instagram_username}</p>}
                                    </div>
                                </div>
                            </section>

                            {/* Submit Buttons */}
                            <div className="flex items-center justify-end space-x-4 pt-6 mt-8 border-t border-gray-200">
                                <Link
                                    href="/employees"
                                    className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex justify-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors"
                                >
                                    Perbarui
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
