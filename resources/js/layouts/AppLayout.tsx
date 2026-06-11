import { Link, usePage } from '@inertiajs/react';
import { ReactNode } from 'react';

interface NavItem {
    label: string;
    href: string;
}

const engineerNav: NavItem[] = [
    { label: 'Dashboard', href: '/engineer/dashboard' },
    { label: 'Tickets', href: '/engineer/tickets' },
    { label: 'Chat', href: '/engineer/chat' },
    { label: 'Remote', href: '/engineer/tickets' },
];

const tenantNav: NavItem[] = [
    { label: 'Dashboard', href: '/portal/dashboard' },
    { label: 'Tickets', href: '/portal/tickets' },
    { label: 'Groups', href: '/portal/groups' },
];

const customerNav: NavItem[] = [
    { label: 'My Tickets', href: '/support/tickets' },
    { label: 'New Ticket', href: '/support/tickets/create' },
    { label: 'Live Chat', href: '/support/chat' },
];

export default function AppLayout({ children, title }: { children: ReactNode; title?: string }) {
    const { auth } = usePage<{ auth: { user: { name: string; role: string } | null } }>().props;
    const role = auth?.user?.role ?? '';

    const nav =
        role === 'engineer' ? engineerNav
        : role === 'tenant_admin' ? tenantNav
        : customerNav;

    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-white border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex h-14 items-center justify-between">
                    <div className="flex items-center gap-6">
                        <span className="font-semibold text-gray-900">InteTeam Support</span>
                        {nav.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                className="text-sm text-gray-600 hover:text-gray-900"
                            >
                                {item.label}
                            </Link>
                        ))}
                    </div>
                    <div className="flex items-center gap-4">
                        {auth?.user && (
                            <span className="text-sm text-gray-500">{auth.user.name}</span>
                        )}
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="text-sm text-gray-500 hover:text-gray-900"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </nav>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {title && (
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">{title}</h1>
                )}
                {children}
            </main>
        </div>
    );
}
