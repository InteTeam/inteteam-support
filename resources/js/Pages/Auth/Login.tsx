import { Head } from '@inertiajs/react';

interface Props {
    ssoEnabled: boolean;
    ssoUrl: string;
}

export default function Login({ ssoEnabled, ssoUrl }: Props) {
    return (
        <>
            <Head title="Sign in" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
                    <h1 className="text-2xl font-bold text-center text-gray-900">
                        InteTeam Support
                    </h1>
                    {ssoEnabled ? (
                        <a
                            href={ssoUrl}
                            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Sign in with InteTeam SSO
                        </a>
                    ) : (
                        <p className="text-center text-sm text-gray-500">
                            SSO is not configured. Contact your administrator.
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}
