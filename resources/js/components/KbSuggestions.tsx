interface KbArticle {
    title: string;
    url: string;
    excerpt: string;
}

interface Props {
    articles: KbArticle[];
    loading: boolean;
}

export default function KbSuggestions({ articles, loading }: Props) {
    if (!loading && articles.length === 0) return null;

    return (
        <div className="mt-3 rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p className="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">
                Did you check these guides?
            </p>

            {loading && (
                <div className="flex items-center gap-2 text-sm text-blue-600">
                    <span className="inline-block w-3 h-3 border-2 border-blue-400 border-t-transparent rounded-full animate-spin" />
                    Looking up articles…
                </div>
            )}

            {!loading && articles.length > 0 && (
                <ul className="space-y-2">
                    {articles.map((article, i) => (
                        <li key={i}>
                            <a
                                href={article.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="block group"
                            >
                                <p className="text-sm font-medium text-blue-800 group-hover:underline">
                                    {article.title}
                                </p>
                                {article.excerpt && (
                                    <p className="text-xs text-blue-600 mt-0.5 line-clamp-2">
                                        {article.excerpt}
                                    </p>
                                )}
                            </a>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
