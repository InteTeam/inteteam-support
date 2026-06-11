import { useEffect, useRef, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import KbSuggestions from '@/components/KbSuggestions';

interface KbArticle { title: string; url: string; excerpt: string }

interface Props {
    usage: { percent: number };
    context: { app?: string; page?: string };
    kbEnabled: boolean;
}

export default function CustomerTicketCreate({ usage, context, kbEnabled }: Props) {
    const errors = usePage<{ errors: Record<string, string> }>().props.errors;

    const form = useForm({
        category: 'other' as string,
        description: '',
        app: context.app ?? '',
        page: context.page ?? '',
    });

    const [kbArticles, setKbArticles]   = useState<KbArticle[]>([]);
    const [kbLoading, setKbLoading]     = useState(false);
    const debounceRef                    = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        if (!kbEnabled || form.data.description.length < 10) {
            setKbArticles([]);
            return;
        }

        if (debounceRef.current) clearTimeout(debounceRef.current);

        debounceRef.current = setTimeout(async () => {
            setKbLoading(true);
            try {
                const res = await window.axios.post('/support/kb/suggest', {
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
    }, [form.data.description, kbEnabled]);

    const atLimit = usage.percent >= 100;

    function submit(e: React.FormEvent) {
        e.preventDefault();
        form.post('/support/tickets');
    }

    return (
        <AppLayout title="Get Help">
            <Head title="Open a Ticket" />

            {usage.percent >= 80 && !atLimit && (
                <div className="mb-4 rounded bg-yellow-50 border border-yellow-200 px-4 py-2 text-yellow-800 text-sm">
                    Support tickets are nearly at capacity this month. Please be concise.
                </div>
            )}
            {atLimit && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-2 text-red-800 text-sm">
                    We're unable to accept new tickets this month. Please contact your account manager.
                </div>
            )}
            {errors.limit && (
                <div className="mb-4 rounded bg-red-50 border border-red-200 px-4 py-2 text-red-800 text-sm">{errors.limit}</div>
            )}

            <form onSubmit={submit} className="max-w-xl bg-white rounded-lg border border-gray-200 p-6 space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">What type of issue?</label>
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
                        Describe your problem <span className="text-red-500">*</span>
                    </label>
                    <textarea
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                        rows={5}
                        placeholder="What happened? Please include as much detail as possible."
                        className="w-full rounded border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    {errors.description && <p className="text-xs text-red-600 mt-1">{errors.description}</p>}

                    <KbSuggestions articles={kbArticles} loading={kbLoading} />
                </div>

                <div className="flex justify-end gap-3 pt-2">
                    <a href="/support/tickets" className="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <button
                        type="submit"
                        disabled={form.processing || atLimit}
                        className="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
