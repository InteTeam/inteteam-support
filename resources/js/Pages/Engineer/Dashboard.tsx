import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

export default function EngineerDashboard() {
    return (
        <AppLayout title="Engineer Dashboard">
            <Head title="Engineer Dashboard" />
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link
                    href="/engineer/tickets"
                    className="block rounded-lg border border-gray-200 bg-white p-6 hover:border-blue-400 transition"
                >
                    <p className="text-sm font-medium text-gray-500">All Tickets</p>
                    <p className="mt-1 text-2xl font-bold text-gray-900">View →</p>
                </Link>
            </div>
        </AppLayout>
    );
}
