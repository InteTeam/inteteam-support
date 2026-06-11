<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name', 'Inte.Team') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased">
    <header class="sticky top-0 z-50 backdrop-blur-lg bg-white/80 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">IT</span>
                    </div>
                    <span class="font-semibold text-lg">Inte.Team</span>
                </a>
                <a href="/" class="text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">← Back to Home</a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold mb-2">Privacy Policy</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8">Last updated: {{ date('F j, Y') }}</p>
        
        <div class="prose dark:prose-invert max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">1. Introduction</h2>
                <p>Inte.Team ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our business management platform.</p>
                <p>We are based at 43 Norman Rise, EH54 6LY Livingston, Scotland, UK. You can contact us at +44 1506 532247 or via <a href="https://inte.team" class="text-blue-600 hover:underline">inte.team</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">2. Information We Collect</h2>
                <p class="font-medium">Personal Information:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Name and contact details (email, phone number, address)</li>
                    <li>Business information (company name, business type)</li>
                    <li>Account credentials (email, encrypted password)</li>
                    <li>Payment information (processed securely through third-party providers)</li>
                </ul>
                <p class="font-medium mt-4">Usage Data:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>IP address and browser type</li>
                    <li>Pages visited and features used</li>
                    <li>Date and time of access</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">3. How We Use Your Information</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Provide and maintain our services</li>
                    <li>Process bookings and appointments</li>
                    <li>Send invoices and manage payments</li>
                    <li>Communicate with you about your account</li>
                    <li>Improve our platform and develop new features</li>
                    <li>Comply with legal obligations</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">4. Data Storage and Security</h2>
                <p>Your data is stored on secure servers located within the European Union. We implement:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Encryption of data in transit and at rest</li>
                    <li>Two-factor authentication (2FA)</li>
                    <li>Daily automated backups</li>
                    <li>Regular security audits</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">5. Data Sharing</h2>
                <p>We do not sell your personal data. We may share your information with:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Service providers who assist in operating our platform</li>
                    <li>Legal authorities when required by law</li>
                    <li>Third parties with your explicit consent</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">6. Your Rights Under GDPR</h2>
                <p>You have the right to:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Access</strong> – Request a copy of your personal data</li>
                    <li><strong>Rectification</strong> – Request correction of inaccurate data</li>
                    <li><strong>Erasure</strong> – Request deletion of your data</li>
                    <li><strong>Portability</strong> – Request transfer of your data</li>
                    <li><strong>Restriction</strong> – Request limitation of processing</li>
                    <li><strong>Objection</strong> – Object to certain types of processing</li>
                </ul>
                <p class="mt-2">To exercise these rights, contact us at the details above.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">7. Data Retention</h2>
                <p>We retain your personal data for as long as your account is active or as needed to provide services. We may retain certain information as required by law.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">8. Cookies</h2>
                <p>We use <strong>essential cookies only</strong>. These are strictly necessary for the platform to function and do not require your consent under GDPR. We do not use any tracking, analytics, or advertising cookies.</p>
                
                <p class="font-medium mt-4">Cookies we use:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li><strong>Session cookie</strong> – Keeps you logged in while using the platform. Expires when you close your browser or after inactivity.</li>
                    <li><strong>CSRF token</strong> – Protects against cross-site request forgery attacks. Essential for security.</li>
                    <li><strong>Remember me</strong> – Only set if you choose "Remember me" at login. Keeps you logged in for longer periods.</li>
                    <li><strong>Theme preference</strong> – Stores your light/dark mode preference for a better experience.</li>
                </ul>

                <p class="mt-4">Since we only use essential cookies required for the service to work, no cookie consent banner is needed. You can delete cookies at any time through your browser settings, but this may affect your ability to use the platform.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">9. Contact Us</h2>
                <p>Questions about this Privacy Policy? Contact us:</p>
                <ul class="list-none pl-0 space-y-1">
                    <li>📍 43 Norman Rise, EH54 6LY Livingston, Scotland</li>
                    <li>📞 +44 1506 532247</li>
                    <li>🌐 <a href="https://inte.team" class="text-blue-600 hover:underline">inte.team</a></li>
                </ul>
            </section>
        </div>
    </main>

    <footer class="bg-slate-900 dark:bg-slate-950 text-slate-400 py-8 mt-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} Inte.Team. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
