import { useEffect, useRef, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import KbSuggestions from '@/components/KbSuggestions';

interface KbArticle { title: string; url: string; excerpt: string }

interface Usage {
    count: number;
    limit: number;
    percent: number;
}

interface Props {
    usage: Usage;
}

export default function TenantTicketCreate({ usage }: Props) {
    const errors = usePage<{ errors: Record<string, string> }>().props.errors;

    const form = useForm({
        category: 'other' as string,
        description: '',
        app: '',
        page: '',
    });

    const [kbArticles, setKbArticles]   = useState<KbArticle[]>([]);
    const [kbLoading, setKbLoading]     = useState(false);
    const debounceRef                    = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        if (form.data.description.length < 10) {
            setKbArticles([]);
            return;
        }

        if (debounceRef.current) clearTimeout(debounceRef.current);

        debounceRef.current = setTimeout(async () => {
            setKbLoading(true);
            try {
                const res = await window.axios.post('/portal/kb/suggest', {
                    query: form.data.description,
                });
                setKbArticles(res.data.articles ?? []);
            } catch {
                setKbArticles([]);
            } finally {
                setKbLoading(false);
            }
        }, 600);

        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
    }, [form.data.description]);

    const atLimit = usage.limit > 0 && usage.percent >= 100;

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/portal/tickets');
    }

    return (
        <AppLayout title="Open New Ticket">
            <Head title="Open New Ticket" />

            {usage.limit > 0 && usage.percent >= 80 && !atLimit && (
                <div className="mb-4 rounded bg-yellow-50 border border-yellow-200 px-4 py-2 text-yellow-800 text-sm">
                    Warning: you have used {Math.round(usage.percent)}% of your monthly ticket quota ({usage.count}/{usage.limit}).
                </div>
            )}
            {atLimit && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-2 text-red-800 text-sm">
                    Monthly ticket limit reached ({usage.limit}/{usage.limit}). You cannot create more tickets this month.
                </div>
            )}
            {errors.limit && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-2 text-red-800 text-sm">{errors.limit}</div>
            )}

            <form onSubmit={submit} className="max-w-xl bg-white rounded-lg border border-gray-200 p-6 space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        value={form.data.category}
                        onChange={(e) => form.setData('category', e.target.value)}
                        className="w-full rounded border border-gray-300 text-sm px-3 py-2"
                    >
                        {['hardware', 'software', 'billing', 'other'].map((c) => (
                            <option key={c} value={c}>{c}</option>
                        ))}
                    </select>
                    {errors.category && <p className="text-xs text-red-600 mt-1">{errors.category}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Description <span className="text-red-500">*</span>
                    </label>
                    <textarea
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                        rows={5}
                        placeholder="Describe the issue in detail…"
                        className="w-full rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    {errors.description && <p className="text-xs text-red-600 mt-1">{errors.description}</p>}

                    <KbSuggestions articles={kbArticles} loading={kbLoading} />
                </div>

                <details className="text-sm">
                    <summary className="text-gray-500 cursor-pointer">Context (optional)</summary>
                    <div className="mt-3 space-y-3">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">App</label>
                            <input
                                type="text"
                                value={form.data.app}
                                onChange={(e) => form.setData('app', e.target.value)}
                                placeholder="e.g. inteteam-crm"
                                className="w-full rounded border border-gray-300 text-sm px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Page</label>
                            <input
                                type="text"
                                value={form.data.page}
                                onChange={(e) => form.setData('page', e.target.value)}
                                placeholder="e.g. /inventory/items"
                                className="w-full rounded border border-gray-300 text-sm px-3 py-2"
                            />
                        </div>
                    </div>
                </details>

                <div className="flex justify-end gap-3 pt-2">
                    <a href="/portal/tickets" className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <button
                        type="submit"
                        disabled={form.processing || atLimit}
                        className="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        Submit Ticket
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
