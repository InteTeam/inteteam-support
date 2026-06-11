import { useEffect, useRef, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import KbSuggestions from '@/components/KbSuggestions';

interface KbArticle { title: string; url: string; excerpt: string }
interface Author { id: number; name: string; role: string }
interface Message {
    id: string;
    author: Author | null;
    body: string;
    sent_at: string;
}
interface ActiveSession {
    id: string;
    status: string;
    agent: { id: number; name: string } | null;
    messages: Message[];
}
interface Props {
    activeSession: ActiveSession | null;
    kbEnabled: boolean;
}

export default function CustomerChatWidget({ activeSession, kbEnabled }: Props) {
    const flash = usePage<{ flash: { warning?: string }; errors: Record<string, string> }>().props;
    const [messages, setMessages]       = useState<Message[]>(activeSession?.messages ?? []);
    const [body, setBody]               = useState('');
    const [sending, setSending]         = useState(false);
    const bottomRef                     = useRef<HTMLDivElement>(null);

    // KB state — only relevant on the start (no active session) screen
    const [issueText, setIssueText]     = useState('');
    const [kbArticles, setKbArticles]   = useState<KbArticle[]>([]);
    const [kbLoading, setKbLoading]     = useState(false);
    const debounceRef                   = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    useEffect(() => {
        if (!activeSession || !window.Echo) return;
        const channel = window.Echo.private(`chat-session.${activeSession.id}`);
        channel.listen('.message.sent', (data: Message) => {
            setMessages((prev) => [...prev, data]);
        });
        return () => window.Echo.leave(`chat-session.${activeSession.id}`);
    }, [activeSession?.id]);

    // Debounced KB lookup — only fires when no active session and kbEnabled
    useEffect(() => {
        if (activeSession || !kbEnabled || issueText.length < 10) {
            setKbArticles([]);
            return;
        }
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(async () => {
            setKbLoading(true);
            try {
                const res = await window.axios.post('/support/kb/suggest', { query: issueText });
                setKbArticles(res.data.articles ?? []);
            } catch {
                setKbArticles([]);
            } finally {
                setKbLoading(false);
            }
        }, 600);
        return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
    }, [issueText, kbEnabled, activeSession]);

    async function sendMessage(e: React.FormEvent) {
        e.preventDefault();
        if (!body.trim() || !activeSession) return;
        setSending(true);
        try {
            const res = await window.axios.post(`/support/chat/${activeSession.id}/messages`, { body });
            setMessages((prev) => [...prev, res.data]);
            setBody('');
        } finally {
            setSending(false);
        }
    }

    // ── No active session ────────────────────────────────────────────────────
    if (!activeSession) {
        return (
            <AppLayout title="Live Chat">
                <Head title="Live Chat" />

                {flash.errors?.chat && (
                    <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-2 text-red-800 text-sm">
                        {flash.errors.chat}
                    </div>
                )}
                {flash.flash?.warning && (
                    <div className="mb-4 rounded bg-yellow-50 border border-yellow-200 px-4 py-2 text-yellow-800 text-sm">
                        {flash.flash.warning}
                    </div>
                )}

                <div className="max-w-md mx-auto mt-12">
                    <div className="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm space-y-5">
                        <div className="text-center">
                            <div className="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                                <span className="text-2xl">💬</span>
                            </div>
                            <h2 className="text-xl font-bold text-gray-900 mb-2">Start a Live Chat</h2>
                            <p className="text-sm text-gray-500">
                                An engineer will connect with you shortly. If no one is available within 60 seconds,
                                we'll create a support ticket automatically.
                            </p>
                        </div>

                        {kbEnabled && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    What's your issue? (optional — we'll search the knowledge base)
                                </label>
                                <textarea
                                    value={issueText}
                                    onChange={(e) => setIssueText(e.target.value)}
                                    rows={3}
                                    placeholder="Describe what's happening…"
                                    className="w-full rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                                <KbSuggestions articles={kbArticles} loading={kbLoading} />
                            </div>
                        )}

                        <button
                            onClick={() => router.post('/support/chat')}
                            className="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                        >
                            Start Chat
                        </button>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // ── Active / queued session ──────────────────────────────────────────────
    return (
        <AppLayout title="Live Chat">
            <Head title="Live Chat" />

            <div className="max-w-2xl mx-auto">
                <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-2">
                        {activeSession.status === 'queued' && (
                            <>
                                <span className="w-2 h-2 rounded-full bg-yellow-400 animate-pulse" />
                                <span className="text-sm text-gray-600">Waiting for an engineer…</span>
                            </>
                        )}
                        {activeSession.status === 'active' && (
                            <>
                                <span className="w-2 h-2 rounded-full bg-green-500" />
                                <span className="text-sm text-gray-600">
                                    Connected with {activeSession.agent?.name ?? 'Support'}
                                </span>
                            </>
                        )}
                        {['closed', 'converted_to_ticket'].includes(activeSession.status) && (
                            <span className="text-sm text-gray-400 capitalize">
                                Session {activeSession.status.replace('_', ' ')}
                            </span>
                        )}
                    </div>
                </div>

                <div className="bg-white rounded-lg border border-gray-200 flex flex-col" style={{ height: '65vh' }}>
                    <div className="flex-1 overflow-y-auto p-4 space-y-3">
                        {activeSession.status === 'queued' && (
                            <p className="text-center text-gray-400 text-sm mt-8">
                                You're in the queue. An engineer will connect shortly.
                            </p>
                        )}
                        {messages.map((msg) => {
                            const isMe = msg.author?.role === 'end_customer';
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

                    {activeSession.status === 'active' && (
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
                </div>
            </div>
        </AppLayout>
    );
}
