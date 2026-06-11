import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Ticket {
    id: string;
    category: string;
    status: string;
    description: string;
    created_at: string;
    customer: { id: number; name: string } | null;
    assignee: { id: number; name: string } | null;
}

interface Usage {
    count: number;
    limit: number;
    percent: number;
}

interface Props {
    tickets: {
        data: Ticket[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { status?: string; category?: string };
    usage: Usage;
}

const STATUS_COLORS: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-600',
};

export default function TenantTicketsIndex({ tickets, filters, usage }: Props) {
    function setFilter(key: string, value: string) {
        router.get('/portal/tickets', { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    const barColor = usage.percent >= 100 ? 'bg-red-500' : usage.percent >= 80 ? 'bg-yellow-400' : 'bg-blue-500';

    return (
        <AppLayout title="Support Tickets">
            <Head title="Support Tickets" />

            {/* Usage bar */}
            {usage.limit > 0 && (
                <div className="mb-6 bg-white rounded-lg border border-gray-200 p-4">
                    <div className="flex justify-between text-sm mb-1">
                        <span className="text-gray-600">Monthly ticket usage</span>
                        <span className={usage.percent >= 80 ? 'font-medium text-orange-600' : 'text-gray-500'}>
                            {usage.count} / {usage.limit}
                        </span>
                    </div>
                    <div className="h-2 rounded bg-gray-100">
                        <div className={`h-2 rounded ${barColor} transition-all`} style={{ width: `${Math.min(100, usage.percent)}%` }} />
                    </div>
                </div>
            )}

            <div className="flex items-center justify-between mb-4">
                <div className="flex gap-3">
                    <select
                        value={filters.status ?? ''}
                        onChange={(e) => setFilter('status', e.target.value)}
                        className="rounded border border-gray-300 text-sm px-3 py-1.5"
                    >
                        <option value="">All statuses</option>
                        {['open', 'in_progress', 'resolved', 'closed'].map((s) => (
                            <option key={s} value={s}>{s.replace('_', ' ')}</option>
                        ))}
                    </select>
                    <select
                        value={filters.category ?? ''}
                        onChange={(e) => setFilter('category', e.target.value)}
                        className="rounded border border-gray-300 text-sm px-3 py-1.5"
                    >
                        <option value="">All categories</option>
                        {['hardware', 'software', 'billing', 'other'].map((c) => (
                            <option key={c} value={c}>{c}</option>
                        ))}
                    </select>
                </div>
                <Link
                    href="/portal/tickets/create"
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
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Submitted by</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Assigned</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Created</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {tickets.data.length === 0 && (
                            <tr><td colSpan={6} className="px-4 py-6 text-center text-gray-400">No tickets.</td></tr>
                        )}
                        {tickets.data.map((ticket) => (
                            <tr key={ticket.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/portal/tickets/${ticket.id}`} className="text-blue-600 hover:underline">
                                        #{ticket.id.slice(-8)}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">{ticket.customer?.name ?? '—'}</td>
                                <td className="px-4 py-3 capitalize">{ticket.category}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${STATUS_COLORS[ticket.status]}`}>
                                        {ticket.status.replace('_', ' ')}
                                    </span>
                                </td>
                                <td className="px-4 py-3">{ticket.assignee?.name ?? <span className="text-gray-400">—</span>}</td>
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
