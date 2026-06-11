import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: string;
    body: string;
    created_at: string;
    author: { id: number; name: string } | null;
}

interface Ticket {
    id: string;
    category: string;
    status: string;
    description: string;
    created_at: string;
    assignee: { id: number; name: string } | null;
    public_notes: Note[];
}

interface Props {
    ticket: Ticket;
}

const STATUS_COLORS: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-600',
};

export default function CustomerTicketShow({ ticket }: Props) {
    return (
        <AppLayout>
            <Head title={`Ticket #${ticket.id.slice(-8)}`} />

            <Link href="/support/tickets" className="text-sm text-blue-600 hover:underline mb-4 block">← My Tickets</Link>

            <div className="max-w-2xl space-y-6">
                <div className="bg-white rounded-lg border border-gray-200 p-6">
                    <div className="flex items-start justify-between mb-4">
                        <h2 className="font-semibold text-gray-900">Ticket #{ticket.id.slice(-8)}</h2>
                        <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${STATUS_COLORS[ticket.status]}`}>
                            {ticket.status.replace('_', ' ')}
                        </span>
                    </div>
                    <dl className="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div><dt className="text-gray-500">Category</dt><dd className="font-medium capitalize">{ticket.category}</dd></div>
                        <div><dt className="text-gray-500">Assigned to</dt><dd>{ticket.assignee?.name ?? 'Awaiting assignment'}</dd></div>
                        <div><dt className="text-gray-500">Opened</dt><dd>{new Date(ticket.created_at).toLocaleDateString('en-GB')}</dd></div>
                    </dl>
                    <div className="border-t border-gray-100 pt-4">
                        <p className="text-sm text-gray-500 mb-1">Your description</p>
                        <p className="text-sm whitespace-pre-wrap">{ticket.description}</p>
                    </div>
                </div>

                <div className="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 className="font-semibold text-gray-900 mb-4">Replies</h3>
                    {ticket.public_notes.length === 0 ? (
                        <p className="text-sm text-gray-400">No replies yet. We'll get back to you shortly.</p>
                    ) : (
                        <div className="space-y-3">
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
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
