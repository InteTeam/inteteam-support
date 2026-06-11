import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Ticket {
    id: string;
    category: string;
    status: string;
    description: string;
    created_at: string;
    tenant: { id: string; name: string; slug: string } | null;
    customer: { id: number; name: string; email: string } | null;
    assignee: { id: number; name: string } | null;
}

interface Tenant {
    id: string;
    name: string;
    slug: string;
}

interface Props {
    tickets: {
        data: Ticket[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    tenants: Tenant[];
    filters: { status?: string; tenant_id?: string; category?: string };
}

const STATUS_LABELS: Record<string, string> = {
    open: 'Open',
    in_progress: 'In Progress',
    resolved: 'Resolved',
    closed: 'Closed',
};

const STATUS_COLORS: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-600',
};

export default function EngineerTicketsIndex({ tickets, tenants, filters }: Props) {
    function setFilter(key: string, value: string) {
        router.get('/engineer/tickets', { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout title="All Tickets">
            <Head title="All Tickets" />

            {/* Filters */}
            <div className="flex flex-wrap gap-3 mb-6">
                <select
                    value={filters.status ?? ''}
                    onChange={(e) => setFilter('status', e.target.value)}
                    className="rounded border border-gray-300 text-sm px-3 py-1.5"
                >
                    <option value="">All statuses</option>
                    {Object.entries(STATUS_LABELS).map(([val, label]) => (
                        <option key={val} value={val}>{label}</option>
                    ))}
                </select>

                <select
                    value={filters.tenant_id ?? ''}
                    onChange={(e) => setFilter('tenant_id', e.target.value)}
                    className="rounded border border-gray-300 text-sm px-3 py-1.5"
                >
                    <option value="">All tenants</option>
                    {tenants.map((t) => (
                        <option key={t.id} value={t.id}>{t.name}</option>
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

            {/* Table */}
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">ID</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Tenant</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Customer</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Category</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Assigned</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-500">Created</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {tickets.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-4 py-6 text-center text-gray-400">No tickets found.</td>
                            </tr>
                        )}
                        {tickets.data.map((ticket) => (
                            <tr key={ticket.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/engineer/tickets/${ticket.id}`} className="text-blue-600 hover:underline">
                                        #{ticket.id.slice(-8)}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">{ticket.tenant?.name ?? '—'}</td>
                                <td className="px-4 py-3">{ticket.customer?.name ?? '—'}</td>
                                <td className="px-4 py-3 capitalize">{ticket.category}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${STATUS_COLORS[ticket.status]}`}>
                                        {STATUS_LABELS[ticket.status]}
                                    </span>
                                </td>
                                <td className="px-4 py-3">{ticket.assignee?.name ?? <span className="text-gray-400">Unassigned</span>}</td>
                                <td className="px-4 py-3 text-gray-500">{new Date(ticket.created_at).toLocaleDateString('en-GB')}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            <div className="flex gap-1 mt-4">
                {tickets.links.map((link, i) => (
                    <Link
                        key={i}
                        href={link.url ?? '#'}
                        className={`px-3 py-1.5 rounded text-sm border ${
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'border-gray-300 text-gray-600 hover:bg-gray-50'
                        } ${!link.url ? 'opacity-40 pointer-events-none' : ''}`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </AppLayout>
    );
}
