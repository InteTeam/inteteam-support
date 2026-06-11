import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Group {
    id: string;
    name: string;
    features: Record<string, boolean> | null;
    users_count: number;
}

interface Props {
    groups: Group[];
}

const FEATURES = [
    { key: 'chat',   label: 'Live Chat',          description: 'Customers can start real-time chat sessions with engineers.' },
    { key: 'remote', label: 'Remote Desktop',     description: 'Engineers can request remote desktop sessions.' },
    { key: 'kb',     label: 'KB Suggestions',     description: 'Surface knowledge base articles in ticket creation and chat start.' },
];

export default function TenantGroupsIndex({ groups }: Props) {
    const flash = usePage<{ flash: { success?: string } }>().props.flash;

    function toggle(groupId: string, feature: string, enabled: boolean) {
        router.patch(`/portal/groups/${groupId}/features`, { feature, enabled });
    }

    return (
        <AppLayout title="Customer Groups">
            <Head title="Customer Groups" />

            {flash?.success && (
                <div className="mb-4 rounded bg-green-50 border border-green-200 px-4 py-2 text-green-800 text-sm">
                    {flash.success}
                </div>
            )}

            {groups.length === 0 && (
                <div className="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-400 text-sm">
                    No groups configured. Groups are created when tenants are provisioned.
                </div>
            )}

            <div className="space-y-4">
                {groups.map((group) => (
                    <div key={group.id} className="bg-white rounded-lg border border-gray-200 p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h2 className="font-semibold text-gray-900">{group.name}</h2>
                                <p className="text-sm text-gray-500">{group.users_count} customer{group.users_count !== 1 ? 's' : ''}</p>
                            </div>
                        </div>

                        <div className="divide-y divide-gray-100">
                            {FEATURES.map(({ key, label, description }) => {
                                const enabled = group.features?.[key] === true;
                                return (
                                    <div key={key} className="flex items-center justify-between py-3">
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">{label}</p>
                                            <p className="text-xs text-gray-500">{description}</p>
                                        </div>
                                        <button
                                            onClick={() => toggle(group.id, key, !enabled)}
                                            className={`relative inline-flex h-5 w-9 rounded-full transition-colors ${enabled ? 'bg-blue-600' : 'bg-gray-200'}`}
                                        >
                                            <span
                                                className={`inline-block h-4 w-4 mt-0.5 rounded-full bg-white shadow transition-transform ${enabled ? 'translate-x-4' : 'translate-x-0.5'}`}
                                            />
                                        </button>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
}
