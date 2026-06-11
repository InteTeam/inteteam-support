import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

export default function CustomerDashboard() {
    return (
        <AppLayout title="Support">
            <Head title="Support" />
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link
                    href="/support/tickets/create"
                    className="block rounded-lg border border-gray-200 bg-white p-6 hover:border-blue-400 transition"
                >
                    <p className="text-sm font-medium text-gray-500">Need help?</p>
                    <p className="mt-1 text-2xl font-bold text-gray-900">Open a Ticket →</p>
                </Link>
                <Link
                    href="/support/tickets"
                    className="block rounded-lg border border-gray-200 bg-white p-6 hover:border-blue-400 transition"
                >
                    <p className="text-sm font-medium text-gray-500">My Tickets</p>
                    <p className="mt-1 text-2xl font-bold text-gray-900">View →</p>
                </Link>
            </div>
        </AppLayout>
    );
}
