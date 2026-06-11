import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Ticket {
    id: string;
    category: string;
    status: string;
    description: string;
    created_at: string;
    assignee: { id: number; name: string } | null;
}

interface Props {
    tickets: {
        data: Ticket[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}

const STATUS_COLORS: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-600',
};

export default function CustomerTicketsIndex({ tickets }: Props) {
    return (
        <AppLayout title="My Tickets">
            <Head title="My Tickets" />

            <div className="flex justify-end mb-4">
                <Link
                    href="/support/tickets/create"
                    className="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                >
                    New Ticket
                </Link>
            </div>

            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">ID</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Assigned to</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Opened</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {tickets.data.length === 0 && (
                            <tr><td colSpan={5} className="px-4 py-6 text-center text-gray-400">No tickets yet.</td></tr>
                        )}
                        {tickets.data.map((ticket) => (
                            <tr key={ticket.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/support/tickets/${ticket.id}`} className="text-blue-600 hover:underline">
                                        #{ticket.id.slice(-8)}
                                    </Link>
                                </td>
                                <td className="px-4 py-3 capitalize">{ticket.category}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${STATUS_COLORS[ticket.status]}`}>
                                        {ticket.status.replace('_', ' ')}
                                    </span>
                                </td>
                                <td className="px-4 py-3">{ticket.assignee?.name ?? <span className="text-gray-400">Awaiting</span>}</td>
                                <td className="px-4 py-3 text-gray-500">{new Date(ticket.created_at).toLocaleDateString('en-GB')}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="flex gap-1 mt-4">
                {tickets.links.map((link, i) => (
                    <Link
                        key={i}
                        href={link.url ?? '#'}
                        className={`px-3 py-1.5 rounded text-sm border ${link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'} ${!link.url ? 'opacity-40 pointer-events-none' : ''}`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </AppLayout>
    );
}
