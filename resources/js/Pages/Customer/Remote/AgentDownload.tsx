import { useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    session: { id: string; session_token: string; status: string } | null;
    downloadUrl: string;
}

export default function CustomerRemoteAgentDownload({ session, downloadUrl }: Props) {
    const [downloaded, setDownloaded] = useState(false);

    const handleAgentReady = () => {
        if (!session) return;
        window.axios.post(`/support/remote/${session.id}/agent-ready`).then(() => {
            router.visit(`/support/remote/${session.id}/waiting`);
        });
    };

    return (
        <AppLayout title="Start Remote Session">
            <div className="max-w-2xl mx-auto space-y-6">
                <div className="bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <h2 className="text-lg font-semibold text-blue-900">Install the InteTeam Remote Agent</h2>
                    <p className="mt-1 text-sm text-blue-800">
                        The agent runs as a background service on your computer. It connects only when a
                        support session is active and stops automatically when the session ends.
                    </p>
                </div>

                {/* Step 1 — Download */}
                <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                    <div className="flex items-center gap-3">
                        <span className="flex-shrink-0 w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">
                            1
                        </span>
                        <h3 className="font-medium text-gray-900">Download the agent</h3>
                    </div>
                    <a
                        href={downloadUrl}
                        onClick={() => setDownloaded(true)}
                        className="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700"
                        download
                    >
                        Download InteTeamRemoteAgent.exe
                        <span className="text-xs text-gray-400">Windows 11</span>
                    </a>
                </div>

                {/* Step 2 — Install */}
                <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-3">
                    <div className="flex items-center gap-3">
                        <span className="flex-shrink-0 w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">
                            2
                        </span>
                        <h3 className="font-medium text-gray-900">Run the installer</h3>
                    </div>
                    <ol className="list-decimal list-inside text-sm text-gray-700 space-y-2 pl-2">
                        <li>Open <strong>PowerShell as Administrator</strong></li>
                        <li>Navigate to your Downloads folder: <code className="bg-gray-100 px-1 rounded">cd ~\Downloads</code></li>
                        <li>Run: <code className="bg-gray-100 px-1 rounded">
                            {session
                                ? `.\\ install.ps1 -Token "${session.session_token}" -Silent`
                                : '.\\ install.ps1 -Token "<your-token>" -Silent'}
                        </code></li>
                        <li>The agent will install and start automatically</li>
                    </ol>
                    <p className="text-xs text-gray-500">
                        The installer needs Administrator rights to register a Windows Service.
                        Future sessions will use the same service — no reinstall needed.
                    </p>
                </div>

                {/* Step 3 — Notify */}
                {session && (
                    <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                        <div className="flex items-center gap-3">
                            <span className="flex-shrink-0 w-7 h-7 rounded-full bg-blue-600 text-white text-sm font-bold flex items-center justify-center">
                                3
                            </span>
                            <h3 className="font-medium text-gray-900">Let the engineer know you are ready</h3>
                        </div>
                        <p className="text-sm text-gray-600">
                            Once the installer finishes, click the button below to notify the engineer.
                        </p>
                        <button
                            onClick={handleAgentReady}
                            disabled={!downloaded}
                            className="px-4 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Agent is installed — start session
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
