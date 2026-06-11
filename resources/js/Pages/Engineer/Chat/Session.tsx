import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

interface Author { id: number; name: string; role: string }

interface Message {
    id: string;
    author: Author | null;
    body: string;
    sent_at: string;
}

interface Session {
    id: string;
    status: string;
    customer: { id: number; name: string } | null;
    tenant: { id: string; name: string } | null;
    agent: { id: number; name: string } | null;
    messages: Message[];
}

interface Props {
    session: Session;
}

export default function EngineerChatSession({ session }: Props) {
    const [messages, setMessages] = useState<Message[]>(session.messages);
    const [body, setBody] = useState('');
    const [sending, setSending] = useState(false);
    const bottomRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    useEffect(() => {
        if (!window.Echo) return;

        const channel = window.Echo.private(`chat-session.${session.id}`);

        channel.listen('.message.sent', (data: Message) => {
            setMessages((prev) => [...prev, data]);
        });

        return () => {
            window.Echo.leave(`chat-session.${session.id}`);
        };
    }, [session.id]);

    async function sendMessage(e: React.FormEvent) {
        e.preventDefault();
        if (!body.trim()) return;

        setSending(true);
        try {
            const res = await window.axios.post(`/engineer/chat/${session.id}/messages`, { body });
            setMessages((prev) => [...prev, res.data]);
            setBody('');
        } finally {
            setSending(false);
        }
    }

    return (
        <AppLayout>
            <Head title={`Chat — ${session.customer?.name}`} />

            <div className="flex items-center justify-between mb-4">
                <div>
                    <Link href="/engineer/chat" className="text-sm text-blue-600 hover:underline">← Queue</Link>
                    <h1 className="mt-1 text-xl font-bold text-gray-900">
                        {session.customer?.name} <span className="text-sm font-normal text-gray-500">({session.tenant?.name})</span>
                    </h1>
                </div>
                {session.status === 'active' && (
                    <div className="flex gap-2">
                        <button
                            onClick={() => router.post(`/engineer/chat/${session.id}/convert`)}
                            className="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50"
                        >
                            Convert to Ticket
                        </button>
                        <button
                            onClick={() => router.post(`/engineer/chat/${session.id}/close`)}
                            className="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Close
                        </button>
                    </div>
                )}
            </div>

            {/* Messages */}
            <div className="bg-white rounded-lg border border-gray-200 flex flex-col" style={{ height: '60vh' }}>
                <div className="flex-1 overflow-y-auto p-4 space-y-3">
                    {messages.length === 0 && (
                        <p className="text-center text-gray-400 text-sm mt-8">No messages yet.</p>
                    )}
                    {messages.map((msg) => {
                        const isMe = msg.author?.role === 'engineer';
                        return (
                            <div key={msg.id} className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}>
                                <div className={`max-w-[70%] rounded-lg px-3 py-2 text-sm ${isMe ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900'}`}>
                                    {!isMe && <p className="text-xs font-medium mb-1 text-gray-500">{msg.author?.name}</p>}
                                    <p className="whitespace-pre-wrap">{msg.body}</p>
                                    <p className={`text-xs mt-1 ${isMe ? 'text-blue-200' : 'text-gray-400'}`}>
                                        {new Date(msg.sent_at).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}
                                    </p>
                                </div>
                            </div>
                        );
                    })}
                    <div ref={bottomRef} />
                </div>

                {session.status === 'active' && (
                    <form onSubmit={sendMessage} className="border-t border-gray-200 p-3 flex gap-2">
                        <input
                            type="text"
                            value={body}
                            onChange={(e) => setBody(e.target.value)}
                            placeholder="Type a message…"
                            className="flex-1 rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        />
                        <button
                            type="submit"
                            disabled={sending || !body.trim()}
                            className="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                        >
                            Send
                        </button>
                    </form>
                )}
                {session.status !== 'active' && (
                    <div className="border-t border-gray-200 p-3 text-center text-sm text-gray-400 capitalize">
                        Session {session.status.replace('_', ' ')}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
