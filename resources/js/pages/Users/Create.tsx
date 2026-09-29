import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';
import { PageProps, Seksi } from '../../types';

interface CreateProps extends PageProps {
    seksis: Seksi[];
}

export default function Create({ seksis }: CreateProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'Staff',
        status: 'Aktif',
        seksi_id: '',
        photo: null as File | null,
    });

    const [photoPreview, setPhotoPreview] = useState<string | null>(null);

    const handlePhotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setData('photo', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPhotoPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        } else {
            setData('photo', null);
            setPhotoPreview(null);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/users');
    };

    return (
        <AuthenticatedLayout>
            <Head title="Tambah User" />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div className="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 className="text-lg leading-6 font-medium text-gray-900">Tambah User</h3>
                            <p className="mt-1 max-w-2xl text-sm text-gray-500">
                                Tambahkan akun pengguna baru ke dalam sistem.
                            </p>
                        </div>
                    </div>
                    <div className="px-4 py-5 sm:p-6">
                        <form onSubmit={submit} className="space-y-6 max-w-3xl">
                            {/* Photo */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Foto</label>
                                <div className="mt-2 flex items-center space-x-4">
                                    <div className="h-16 w-16 bg-gray-200 rounded-full overflow-hidden flex items-center justify-center">
                                        {photoPreview ? (
                                            <img src={photoPreview} alt="Preview" className="h-16 w-16 object-cover" />
                                        ) : (
                                            <svg className="h-10 w-10 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        )}
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/jpg"
                                        onChange={handlePhotoChange}
                                        className="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                </div>
                                {errors.photo && <p className="mt-2 text-sm text-red-600">{errors.photo}</p>}
                            </div>

                            {/* Name */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Nama <span className="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                        errors.name ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                    }`}
                                    required
                                />
                                {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
                            </div>

                            {/* Email */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Email <span className="text-red-500">*</span></label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                        errors.email ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                    }`}
                                    required
                                />
                                {errors.email && <p className="mt-2 text-sm text-red-600">{errors.email}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Role */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Role <span className="text-red-500">*</span></label>
                                    <select
                                        value={data.role}
                                        onChange={(e) => setData('role', e.target.value as any)}
                                        className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                            errors.role ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                        }`}
                                        required
                                    >
                                        <option value="Staff">Staff</option>
                                        <option value="Administrator">Administrator</option>
                                    </select>
                                    {errors.role && <p className="mt-2 text-sm text-red-600">{errors.role}</p>}
                                </div>

                                {/* Status */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Status <span className="text-red-500">*</span></label>
                                    <select
                                        value={data.status}
                                        onChange={(e) => setData('status', e.target.value as any)}
                                        className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                            errors.status ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                        }`}
                                        required
                                    >
                                        <option value="Aktif">Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                    {errors.status && <p className="mt-2 text-sm text-red-600">{errors.status}</p>}
                                </div>
                            </div>

                            {/* Seksi */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Seksi</label>
                                <select
                                    value={data.seksi_id}
                                    onChange={(e) => setData('seksi_id', e.target.value)}
                                    className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                        errors.seksi_id ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                    }`}
                                >
                                    <option value="">Pilih Seksi</option>
                                    {seksis.map(seksi => (
                                        <option key={seksi.id} value={seksi.id}>{seksi.nama}</option>
                                    ))}
                                </select>
                                {errors.seksi_id && <p className="mt-2 text-sm text-red-600">{errors.seksi_id}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Password */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Password <span className="text-red-500">*</span></label>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                            errors.password ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                        }`}
                                        required
                                    />
                                    {errors.password && <p className="mt-2 text-sm text-red-600">{errors.password}</p>}
                                </div>

                                {/* Password Confirmation */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Konfirmasi Password <span className="text-red-500">*</span></label>
                                    <input
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        className={`mt-1 block w-full rounded-md shadow-sm sm:text-sm ${
                                            errors.password_confirmation ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                                        }`}
                                        required
                                    />
                                </div>
                            </div>

                            <div className="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                                <Link
                                    href="/users"
                                    className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                >
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
