import React from 'react';
import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-gray-900">
            <Head title="Welcome" />
            <div className="max-w-2xl text-center p-8 bg-white rounded-lg shadow-md">
                <h1 className="text-4xl font-bold mb-4 text-blue-600">Instagram Engagement Tracking</h1>
                <p className="text-lg text-gray-600 font-medium">Laravel 13 + Inertia + React + TypeScript</p>
            </div>
        </div>
    );
}
