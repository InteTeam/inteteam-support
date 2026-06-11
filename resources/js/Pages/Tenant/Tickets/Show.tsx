import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: string;
    body: string;
    is_internal: false;
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
    customer: { id: number; name: string; email: string } | null;
    assignee: { id: number; name: string } | null;
    public_notes: Note[];
}

interface Props {
    ticket: Ticket;
}

export default function TenantTicketShow({ ticket }: Props) {
    const flash = usePage<{ flash: { success?: string } }>().props.flash;
    const noteForm = useForm({ body: '' });

    function submitNote(e: React.FormEvent) {
        e.preventDefault();
        noteForm.post(`/portal/tickets/${ticket.id}/notes`, { onSuccess: () => noteForm.reset() });
    }

    return (
        <AppLayout>
            <Head title={`Ticket #${ticket.id.slice(-8)}`} />

            {flash?.success && (
                <div className="mb-4 rounded bg-green-50 border border-green-200 px-4 py-2 text-green-800 text-sm">
                    {flash.success}
                </div>
            )}

            <Link href="/portal/tickets" className="text-sm text-blue-600 hover:underline mb-4 block">← Back to tickets</Link>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    <div className="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 className="font-semibold text-gray-900 mb-4">Ticket #{ticket.id.slice(-8)}</h2>
                        <dl className="grid grid-cols-2 gap-4 text-sm">
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
                        <h2 className="font-semibold text-gray-900 mb-4">Correspondence</h2>
                        <div className="space-y-3">
                            {ticket.public_notes.length === 0 && <p className="text-sm text-gray-400">No replies yet.</p>}
                            {ticket.public_notes.map((note) => (
                                <div key={note.id} className="rounded-lg bg-gray-50 border border-gray-200 p-3 text-sm">
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="font-medium">{note.author?.name ?? 'Support'}</span>
                                        <span className="text-xs text-gray-400">{new Date(note.created_at).toLocaleString('en-GB')}</span>
                                    </div>
                                    <p className="whitespace-pre-wrap">{note.body}</p>
                                </div>
                            ))}
                        </div>

                        {ticket.status !== 'closed' && (
                            <form onSubmit={submitNote} className="mt-4 pt-4 border-t border-gray-100 space-y-3">
                                <textarea
                                    value={noteForm.data.body}
                                    onChange={(e) => noteForm.setData('body', e.target.value)}
                                    rows={3}
                                    placeholder="Add a reply…"
                                    className="w-full rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                                {noteForm.errors.body && <p className="text-xs text-red-600">{noteForm.errors.body}</p>}
                                <button
                                    type="submit"
                                    disabled={noteForm.processing}
                                    className="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Send Reply
                                </button>
                            </form>
                        )}
                    </div>
                </div>

                <div className="bg-white rounded-lg border border-gray-200 p-4 text-sm h-fit">
                    <h3 className="font-semibold text-gray-700 mb-3">Ticket Info</h3>
                    <dl className="space-y-2">
                        <div><dt className="text-gray-500">Submitted by</dt><dd>{ticket.customer?.name}</dd></div>
                        <div><dt className="text-gray-500">Assigned to</dt><dd>{ticket.assignee?.name ?? 'Unassigned'}</dd></div>
                        <div><dt className="text-gray-500">Opened</dt><dd>{new Date(ticket.created_at).toLocaleDateString('en-GB')}</dd></div>
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}
