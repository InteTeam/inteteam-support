import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    session: { id: string; status: string };
}

export default function CustomerRemoteWaiting({ session }: Props) {
    // Poll every 5 s for session status change (engineer connected → active)
    useEffect(() => {
        if (session.status === 'ended' || session.status === 'declined') return;

        const interval = setInterval(() => {
            router.reload({ only: ['session'] });
        }, 5000);

        return () => clearInterval(interval);
    }, [session.status]);

    return (
        <AppLayout title="Waiting for engineer">
            <div className="max-w-md mx-auto text-center space-y-6 mt-12">
                <div className="w-16 h-16 mx-auto border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin" />

                <h2 className="text-xl font-semibold text-gray-900">
                    Waiting for the engineer to connect…
                </h2>
                <p className="text-sm text-gray-500">
                    The session will start automatically once the engineer joins.
                    You can close this tab — the agent will connect them directly.
                </p>

                {session.status === 'ended' && (
                    <p className="text-sm text-gray-700">The session has ended.</p>
                )}
            </div>
        </AppLayout>
    );
}
