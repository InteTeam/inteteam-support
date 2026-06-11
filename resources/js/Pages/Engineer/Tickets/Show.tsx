import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: string;
    body: string;
    is_internal: boolean;
    created_at: string;
    author: { id: number; name: string; role: string } | null;
}

interface Ticket {
    id: string;
    category: string;
    status: string;
    description: string;
    app: string | null;
    page: string | null;
    created_at: string;
    tenant: { id: string; name: string } | null;
    customer: { id: number; name: string; email: string } | null;
    assignee: { id: number; name: string } | null;
    notes: Note[];
}

interface Engineer {
    id: number;
    name: string;
}

interface Props {
    ticket: Ticket;
    engineers: Engineer[];
}

const STATUS_OPTIONS = ['open', 'in_progress', 'resolved', 'closed'];

export default function EngineerTicketShow({ ticket, engineers }: Props) {
    const flash = usePage<{ flash: { success?: string; warning?: string } }>().props.flash;

    const statusForm = useForm({ status: ticket.status });
    const assignForm = useForm({ engineer_id: String(ticket.assignee?.id ?? '') });
    const noteForm = useForm({ body: '', is_internal: false });

    function submitStatus(e: React.FormEvent) {
        e.preventDefault();
        statusForm.patch(`/engineer/tickets/${ticket.id}/status`);
    }

    function submitAssign(e: React.FormEvent) {
        e.preventDefault();
        assignForm.post(`/engineer/tickets/${ticket.id}/assign`);
    }

    function submitNote(e: React.FormEvent) {
        e.preventDefault();
        noteForm.post(`/engineer/tickets/${ticket.id}/notes`, { onSuccess: () => noteForm.reset() });
    }

    return (
        <AppLayout>
            <Head title={`Ticket #${ticket.id.slice(-8)}`} />

            {flash?.success && (
                <div className="mb-4 rounded bg-green-50 border border-green-200 px-4 py-2 text-green-800 text-sm">
                    {flash.success}
                </div>
            )}

            <div className="flex items-center justify-between mb-6">
                <div>
                    <Link href="/engineer/tickets" className="text-sm text-blue-600 hover:underline">← All Tickets</Link>
                    <h1 className="mt-1 text-2xl font-bold text-gray-900">Ticket #{ticket.id.slice(-8)}</h1>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Main */}
                <div className="lg:col-span-2 space-y-6">
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <dl className="grid grid-cols-2 gap-4 text-sm">
                            <div><dt className="text-gray-500">Tenant</dt><dd className="font-medium">{ticket.tenant?.name ?? '—'}</dd></div>
                            <div><dt className="text-gray-500">Customer</dt><dd className="font-medium">{ticket.customer?.name} <span className="text-gray-400">({ticket.customer?.email})</span></dd></div>
                            <div><dt className="text-gray-500">Category</dt><dd className="font-medium capitalize">{ticket.category}</dd></div>
                            <div><dt className="text-gray-500">Status</dt><dd className="font-medium capitalize">{ticket.status.replace('_', ' ')}</dd></div>
                            {ticket.app && <div><dt className="text-gray-500">App</dt><dd className="font-mono text-xs">{ticket.app}</dd></div>}
                            {ticket.page && <div><dt className="text-gray-500">Page</dt><dd className="font-mono text-xs">{ticket.page}</dd></div>}
                        </dl>
                        <div className="mt-4 pt-4 border-t border-gray-100">
                            <p className="text-sm text-gray-500 mb-1">Description</p>
                            <p className="text-sm whitespace-pre-wrap">{ticket.description}</p>
                        </div>
                    </div>

                    {/* Notes */}
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-900 mb-4">Notes</h2>
                        <div className="space-y-3">
                            {ticket.notes.length === 0 && <p className="text-sm text-gray-400">No notes yet.</p>}
                            {ticket.notes.map((note) => (
                                <div key={note.id} className={`rounded-lg p-3 text-sm ${note.is_internal ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50 border border-gray-200'}`}>
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="font-medium">{note.author?.name ?? 'Unknown'}</span>
                                        <div className="flex items-center gap-2">
                                            {note.is_internal && <span className="text-xs bg-yellow-200 text-yellow-800 px-1.5 py-0.5 rounded">Internal</span>}
                                            <span className="text-xs text-gray-400">{new Date(note.created_at).toLocaleString('en-GB')}</span>
                                        </div>
                                    </div>
                                    <p className="whitespace-pre-wrap">{note.body}</p>
                                </div>
                            ))}
                        </div>

                        {/* Add note */}
                        <form onSubmit={submitNote} className="mt-4 pt-4 border-t border-gray-100 space-y-3">
                            <textarea
                                value={noteForm.data.body}
                                onChange={(e) => noteForm.setData('body', e.target.value)}
                                rows={3}
                                placeholder="Add a note…"
                                className="w-full rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <div className="flex items-center justify-between">
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        checked={noteForm.data.is_internal}
                                        onChange={(e) => noteForm.setData('is_internal', e.target.checked)}
                                        className="rounded"
                                    />
                                    Internal note
                                </label>
                                <button
                                    type="submit"
                                    disabled={noteForm.processing}
                                    className="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Add Note
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-4">
                    {/* Status */}
                    <div className="bg-white rounded-lg border border-gray-200 p-4">
                        <h3 className="text-sm font-semibold text-gray-700 mb-3">Update Status</h3>
                        <form onSubmit={submitStatus} className="space-y-2">
                            <select
                                value={statusForm.data.status}
                                onChange={(e) => statusForm.setData('status', e.target.value)}
                                className="w-full rounded border border-gray-300 text-sm px-3 py-1.5"
                            >
                                {STATUS_OPTIONS.map((s) => (
                                    <option key={s} value={s}>{s.replace('_', ' ')}</option>
                                ))}
                            </select>
                            <button
                                type="submit"
                                disabled={statusForm.processing}
                                className="w-full px-3 py-1.5 bg-gray-800 text-white text-sm rounded hover:bg-gray-900 disabled:opacity-50"
                            >
                                Save Status
                            </button>
                        </form>
                    </div>

                    {/* Assign */}
                    <div className="bg-white rounded-lg border border-gray-200 p-4">
                        <h3 className="text-sm font-semibold text-gray-700 mb-3">Assign</h3>
                        <form onSubmit={submitAssign} className="space-y-2">
                            <select
                                value={assignForm.data.engineer_id}
                                onChange={(e) => assignForm.setData('engineer_id', e.target.value)}
                                className="w-full rounded border border-gray-300 text-sm px-3 py-1.5"
                            >
                                <option value="">Unassigned</option>
                                {engineers.map((eng) => (
                                    <option key={eng.id} value={String(eng.id)}>{eng.name}</option>
                                ))}
                            </select>
                            <button
                                type="submit"
                                disabled={assignForm.processing}
                                className="w-full px-3 py-1.5 bg-gray-800 text-white text-sm rounded hover:bg-gray-900 disabled:opacity-50"
                            >
                                Assign
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
