import AppLayout from '@/layouts/AppLayout';

interface Props {
    session: { id: string; created_at: string };
    engineer: { name: string };
    ticket: { id: string; description: string };
    acceptUrl: string;
    declineUrl: string;
}

export default function CustomerRemoteRespondPage({ session, engineer, ticket, acceptUrl, declineUrl }: Props) {
    return (
        <AppLayout title="Remote Desktop Request">
            <div className="max-w-lg mx-auto bg-white border border-gray-200 rounded-xl p-8 space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-gray-900">Screen sharing request</h2>
                    <p className="mt-1 text-sm text-gray-500">
                        {engineer.name} wants to connect to your computer remotely to help with your ticket.
                    </p>
                </div>

                <div className="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">
                    <p className="font-medium">Ticket:</p>
                    <p className="mt-1">{ticket.description}</p>
                </div>

                <p className="text-sm text-gray-600">
                    If you accept, you will be asked to download and run the InteTeam Remote Agent.
                    The engineer will be able to see and control your screen until you or they end the session.
                </p>

                <div className="flex gap-3">
                    <a
                        href={acceptUrl}
                        className="flex-1 text-center px-4 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
                    >
                        Accept
                    </a>
                    <a
                        href={declineUrl}
                        className="flex-1 text-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200"
                    >
                        Decline
                    </a>
                </div>

                <p className="text-xs text-gray-400 text-center">
                    This request was sent at {new Date(session.created_at).toLocaleString()}.
                </p>
            </div>
        </AppLayout>
    );
}
