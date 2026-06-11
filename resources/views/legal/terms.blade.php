<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms and Conditions - {{ config('app.name', 'Inte.Team') }}</title>
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
        <h1 class="text-3xl font-bold mb-2">Terms and Conditions</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8">Last updated: {{ date('F j, Y') }}</p>
        
        <div class="prose dark:prose-invert max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">1. Agreement to Terms</h2>
                <p>By accessing or using the Inte.Team platform ("Service"), you agree to be bound by these Terms and Conditions. If you disagree with any part of these terms, you may not access the Service.</p>
                <p>Inte.Team is operated by Inte.Team, located at 43 Norman Rise, EH54 6LY Livingston, Scotland, UK.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">2. Description of Service</h2>
                <p>Inte.Team provides a business management platform that includes:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Custom forms and booking management</li>
                    <li>Appointment scheduling</li>
                    <li>Invoicing and payment tracking</li>
                    <li>Inventory management</li>
                    <li>Content management (headless CMS)</li>
                    <li>Image galleries</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">3. Account Registration</h2>
                <p>To use our Service, you must:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Be at least 18 years old</li>
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Notify us immediately of any unauthorised access</li>
                </ul>
                <p class="mt-2">You are responsible for all activities that occur under your account.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">4. Acceptable Use</h2>
                <p>You agree not to:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Use the Service for any unlawful purpose</li>
                    <li>Attempt to gain unauthorised access to our systems</li>
                    <li>Interfere with or disrupt the Service</li>
                    <li>Upload malicious code or content</li>
                    <li>Violate any applicable laws or regulations</li>
                    <li>Infringe on the rights of others</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">5. Your Data</h2>
                <p>You retain ownership of all data you upload to the Service. By using our Service, you grant us permission to store, process, and back up your data as necessary to provide the Service.</p>
                <p>We perform daily automated backups to protect your data. For details on how we handle your data, please see our <a href="/privacy" class="text-blue-600 hover:underline">Privacy Policy</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">6. Payment Terms</h2>
                <p>If applicable to your subscription:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Fees are billed in advance on a recurring basis</li>
                    <li>All fees are non-refundable unless otherwise stated</li>
                    <li>We reserve the right to change pricing with reasonable notice</li>
                    <li>Failure to pay may result in suspension of your account</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">7. Custom Feature Development</h2>
                <p>We may develop custom features upon request. Custom development:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Is subject to separate quotation and agreement</li>
                    <li>Remains the intellectual property of Inte.Team unless otherwise agreed</li>
                    <li>May be offered to other customers unless exclusivity is agreed</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">8. Service Availability</h2>
                <p>We strive to maintain high availability but do not guarantee uninterrupted access. We may suspend the Service for:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Scheduled maintenance (with reasonable notice)</li>
                    <li>Emergency repairs</li>
                    <li>Circumstances beyond our control</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">9. Limitation of Liability</h2>
                <p>To the maximum extent permitted by law, Inte.Team shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the Service.</p>
                <p>Our total liability shall not exceed the amount paid by you in the 12 months preceding the claim.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">10. Termination</h2>
                <p>Either party may terminate this agreement at any time. Upon termination:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Your access to the Service will be discontinued</li>
                    <li>You may request export of your data within 30 days</li>
                    <li>We may retain certain data as required by law</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">11. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms at any time. We will provide notice of significant changes. Continued use of the Service after changes constitutes acceptance of the new Terms.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">12. Governing Law</h2>
                <p>These Terms shall be governed by and construed in accordance with the laws of Scotland, United Kingdom. Any disputes shall be subject to the exclusive jurisdiction of the Scottish courts.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold mt-8 mb-4">13. Contact Us</h2>
                <p>Questions about these Terms? Contact us:</p>
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
