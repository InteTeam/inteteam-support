import { Head, usePage } from '@inertiajs/react';

interface Props {
    ssoEnabled: boolean;
    ssoUrl: string;
}

interface PageProps extends Props {
    errors: Record<string, string>;
    flash: {
        status?: string;
    };
}

export default function Login({ ssoEnabled, ssoUrl }: Props) {
    const { errors = {}, flash } = usePage<PageProps>().props;

    return (
        <>
            <Head title="Sign in" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="max-w-md w-full space-y-6 p-8 bg-white rounded-lg shadow">
                    <h1 className="text-2xl font-bold text-center text-gray-900">
                        InteTeam Support
                    </h1>

                    {flash?.status && (
                        <div className="rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                            {flash.status}
                        </div>
                    )}

                    {errors?.sso && (
                        <div className="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                            {errors.sso}
                        </div>
                    )}

                    {ssoEnabled ? (
                        <a
                            href={ssoUrl}
                            className="w-full flex items-center justify-center gap-2 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
                        >
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Sign in with Inte.Team SSO
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
