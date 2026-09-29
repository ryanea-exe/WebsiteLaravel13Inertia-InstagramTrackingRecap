import React, { PropsWithChildren, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '../types';

export default function AuthenticatedLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<PageProps>().props;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-gray-100 flex flex-col md:flex-row">
            {/* Mobile overlay */}
            {isSidebarOpen && (
                <div
                    className="fixed inset-0 z-20 bg-black opacity-50 md:hidden"
                    onClick={() => setIsSidebarOpen(false)}
                ></div>
            )}

            {/* Sidebar */}
            <aside
                className={`fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-md transform transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-0 ${
                    isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex items-center justify-center h-16 border-b border-gray-200">
                    <span className="text-lg font-bold text-gray-800">Tracking App</span>
                </div>
                <nav className="p-4 space-y-1">
                    {/* Dashboard */}
                    <Link
                        href="/"
                        className={`block px-4 py-2 text-sm font-medium rounded-md ${
                            usePage().url === '/' ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-gray-200'
                        }`}
                    >
                        Dashboard
                    </Link>

                    {/* User Management - Only for Admin */}
                    {auth.user.role === 'Administrator' && (
                        <Link
                            href="/users"
                            className={`block px-4 py-2 text-sm font-medium rounded-md ${
                                usePage().url.startsWith('/users') ? 'text-blue-700 bg-blue-100' : 'text-gray-700 hover:bg-gray-200'
                            }`}
                        >
                            User Management
                        </Link>
                    )}
                </nav>
            </aside>

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col overflow-hidden">
                {/* Topbar */}
                <header className="bg-white shadow-sm z-10">
                    <div className="px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex">
                                {/* Hamburger Menu (Mobile) */}
                                <div className="flex items-center md:hidden">
                                    <button
                                        onClick={() => setIsSidebarOpen(!isSidebarOpen)}
                                        className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                                    >
                                        <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path
                                                className={!isSidebarOpen ? 'inline-flex' : 'hidden'}
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M4 6h16M4 12h16M4 18h16"
                                            />
                                            <path
                                                className={isSidebarOpen ? 'inline-flex' : 'hidden'}
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {/* Topbar Right Side (User Info & Logout) */}
                            <div className="hidden md:flex sm:items-center sm:ms-6">
                                <div className="ms-3 relative">
                                    <div className="flex items-center space-x-3 cursor-pointer" onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}>
                                        <div className="flex flex-col text-right">
                                            <span className="text-sm font-medium text-gray-800">{auth.user.name}</span>
                                            <span className="text-xs text-gray-500">{auth.user.role}</span>
                                        </div>
                                        <div className="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                                            {auth.user.name.charAt(0)}
                                        </div>
                                    </div>

                                    {/* Dropdown Menu */}
                                    {showingNavigationDropdown && (
                                        <div className="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-50">
                                            <Link
                                                href="/logout"
                                                method="post"
                                                as="button"
                                                className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                Log Out
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Mobile User Menu Toggle */}
                            <div className="flex items-center md:hidden">
                                <button
                                    onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
                                    className="flex items-center focus:outline-none"
                                >
                                    <div className="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                                        {auth.user.name.charAt(0)}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Mobile Dropdown Menu */}
                    {showingNavigationDropdown && (
                        <div className="md:hidden border-t border-gray-200 bg-white">
                            <div className="px-4 py-3">
                                <div className="text-base font-medium text-gray-800">
                                    {auth.user.name}
                                </div>
                                <div className="text-sm font-medium text-gray-500">{auth.user.role}</div>
                            </div>

                            <div className="border-t border-gray-200 pb-1">
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    className="block w-full text-left px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100"
                                >
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    )}
                </header>

                {/* Page Content */}
                <main className="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
