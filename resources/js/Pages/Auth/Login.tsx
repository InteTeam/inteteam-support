import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface TenantInfo {
    slug: string;
    name: string;
}

interface Props {
    ssoEnabled: boolean;
    ssoUrl: string;
    otpRequestUrl: string;
    otpVerifyUrl: string;
    tenant: TenantInfo | null;
    tenantSlugRequested: string | null;
}

function CustomerOtpForm({
    tenant,
    otpRequestUrl,
    otpVerifyUrl,
}: {
    tenant: TenantInfo;
    otpRequestUrl: string;
    otpVerifyUrl: string;
}) {
    const [step, setStep] = useState<'request' | 'verify'>('request');
    const { errors = {} } = usePage<{ errors: Record<string, string> }>().props;

    const requestForm = useForm({ tenant: tenant.slug, email: '' });
    const verifyForm = useForm({ tenant: tenant.slug, email: '', code: '' });

    const submitRequest = (e: React.FormEvent) => {
        e.preventDefault();
        requestForm.post(otpRequestUrl, {
            preserveScroll: true,
            onSuccess: () => {
                verifyForm.setData('email', requestForm.data.email);
                setStep('verify');
            },
        });
    };

    const submitVerify = (e: React.FormEvent) => {
        e.preventDefault();
        verifyForm.post(otpVerifyUrl, { preserveScroll: true });
    };

    return (
        <div className="space-y-3 border-t border-gray-200 pt-6">
            <p className="text-sm text-gray-600 text-center">
                Sign in to <span className="font-medium">{tenant.name}</span> support
            </p>

            {step === 'request' ? (
                <form onSubmit={submitRequest} className="space-y-3">
                    <input
                        type="email"
                        required
                        placeholder="Your email address"
                        value={requestForm.data.email}
                        onChange={(e) => requestForm.setData('email', e.target.value)}
                        className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    />
                    {errors?.email && <p className="text-sm text-red-600">{errors.email}</p>}
                    <button
                        type="submit"
                        disabled={requestForm.processing}
                        className="w-full py-2 px-4 rounded-md text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 transition-colors disabled:opacity-50"
                    >
                        Send me a code
                    </button>
                </form>
            ) : (
                <form onSubmit={submitVerify} className="space-y-3">
                    <p className="text-sm text-gray-500">
                        Enter the 6-digit code sent to {verifyForm.data.email}.
                    </p>
                    <input
                        type="text"
                        inputMode="numeric"
                        required
                        maxLength={6}
                        placeholder="123456"
                        value={verifyForm.data.code}
                        onChange={(e) => verifyForm.setData('code', e.target.value)}
                        className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm tracking-widest text-center"
                    />
                    {errors?.otp && <p className="text-sm text-red-600">{errors.otp}</p>}
                    <button
                        type="submit"
                        disabled={verifyForm.processing}
                        className="w-full py-2 px-4 rounded-md text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 transition-colors disabled:opacity-50"
                    >
                        Sign in
                    </button>
                    <button
                        type="button"
                        onClick={() => setStep('request')}
                        className="w-full text-sm text-gray-500 hover:text-gray-700"
                    >
                        Use a different email
                    </button>
                </form>
            )}
        </div>
    );
}

export default function Login({ ssoEnabled, ssoUrl, otpRequestUrl, otpVerifyUrl, tenant, tenantSlugRequested }: Props) {
    const { errors = {}, flash } = usePage<{
        errors: Record<string, string>;
        flash: { alert?: string; type?: string };
    }>().props;

    return (
        <>
            <Head title="Sign in" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="max-w-md w-full space-y-6 p-8 bg-white rounded-lg shadow">
                    <h1 className="text-2xl font-bold text-center text-gray-900">
                        InteTeam Support
                    </h1>

                    {flash?.alert && (
                        <div
                            className={`rounded-md border px-4 py-3 text-sm ${
                                flash.type === 'success'
                                    ? 'bg-blue-50 border-blue-200 text-blue-800'
                                    : 'bg-red-50 border-red-200 text-red-800'
                            }`}
                        >
                            {flash.alert}
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

                    {tenant ? (
                        <CustomerOtpForm tenant={tenant} otpRequestUrl={otpRequestUrl} otpVerifyUrl={otpVerifyUrl} />
                    ) : (
                        tenantSlugRequested && (
                            <p className="text-center text-sm text-gray-400 border-t border-gray-200 pt-6">
                                Support link not recognised. Ask your business for a fresh link.
                            </p>
                        )
                    )}
                </div>
            </div>
        </>
    );
}
