import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

interface Customer { id: number; name: string }
interface Tenant { id: string; name: string; slug: string }

interface QueuedSession {
    id: string;
    customer: Customer | null;
    tenant: Tenant | null;
    created_at: string;
}

interface ActiveSession {
    id: string;
    customer: Customer | null;
    tenant: Tenant | null;
}

interface Props {
    queuedSessions: QueuedSession[];
    mySessions: ActiveSession[];
    availability: string;
}

const AVAILABILITY_COLORS: Record<string, string> = {
    online: 'bg-green-500',
    away:   'bg-yellow-400',
    offline:'bg-gray-400',
};

export default function ChatQueue({ queuedSessions: initial, mySessions, availability }: Props) {
    const [queued, setQueued] = useState<QueuedSession[]>(initial);
    const availForm = useForm({ status: availability });

    useEffect(() => {
        const channel = window.Echo?.private('chat-queue');
        if (!channel) return;

        channel.listen('.session.queued', (data: QueuedSession) => {
            setQueued((prev) => [data, ...prev]);
        });

        return () => {
            window.Echo?.leave('chat-queue');
        };
    }, []);

    function accept(sessionId: string) {
        router.post(`/engineer/chat/${sessionId}/accept`);
    }

    function saveAvailability(e: React.FormEvent) {
        e.preventDefault();
        availForm.post('/engineer/chat/availability');
    }

    function elapsed(iso: string): string {
        const s = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
        return s < 60 ? `${s}s ago` : `${Math.floor(s / 60)}m ago`;
    }

    return (
        <AppLayout title="Chat Queue">
            <Head title="Chat Queue" />

            {/* Availability toggle */}
            <div className="flex items-center gap-4 mb-6 bg-white rounded-lg border border-gray-200 p-4">
                <span className={`w-3 h-3 rounded-full ${AVAILABILITY_COLORS[availForm.data.status]}`} />
                <form onSubmit={saveAvailability} className="flex items-center gap-3">
                    <select
                        value={availForm.data.status}
                        onChange={(e) => availForm.setData('status', e.target.value)}
                        className="text-sm rounded border border-gray-300 px-2 py-1"
                    >
                        <option value="online">Online</option>
                        <option value="away">Away</option>
                        <option value="offline">Offline</option>
                    </select>
                    <button
                        type="submit"
                        disabled={availForm.processing}
                        className="px-3 py-1 text-sm bg-gray-800 text-white rounded hover:bg-gray-900 disabled:opacity-50"
                    >
                        Save
                    </button>
                </form>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Waiting queue */}
                <div>
                    <h2 className="text-lg font-semibold text-gray-900 mb-3">
                        Waiting ({queued.length})
                    </h2>
                    {queued.length === 0 ? (
                        <div className="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-400 text-sm">
                            Queue is empty.
                        </div>
                    ) : (
                        <div className="space-y-2">
                            {queued.map((s) => (
                                <div key={s.id} className="bg-white rounded-lg border border-blue-200 p-4 flex items-center justify-between">
                                    <div>
                                        <p className="font-medium text-gray-900">{s.customer?.name ?? 'Unknown'}</p>
                                        <p className="text-xs text-gray-500">{s.tenant?.name} · {elapsed(s.created_at)}</p>
                                    </div>
                                    <button
                                        onClick={() => accept(s.id)}
                                        className="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                                    >
                                        Accept
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* My active sessions */}
                <div>
                    <h2 className="text-lg font-semibold text-gray-900 mb-3">
                        My Active Sessions ({mySessions.length})
                    </h2>
                    {mySessions.length === 0 ? (
                        <div className="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-400 text-sm">
                            No active sessions.
                        </div>
                    ) : (
                        <div className="space-y-2">
                            {mySessions.map((s) => (
                                <Link
                                    key={s.id}
                                    href={`/engineer/chat/${s.id}`}
                                    className="block bg-white rounded-lg border border-green-200 p-4 hover:border-green-400 transition"
                                >
                                    <p className="font-medium text-gray-900">{s.customer?.name ?? 'Unknown'}</p>
                                    <p className="text-xs text-gray-500">{s.tenant?.name}</p>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
