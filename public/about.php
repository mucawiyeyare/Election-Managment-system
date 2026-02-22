<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About Us | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
    <?php include '../includes/navbar.php'; ?>

    <main class="flex-1">
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-700 via-purple-600 to-purple-800 opacity-95"></div>
            <div class="relative container mx-auto px-6 py-20 text-white">
                <div class="max-w-4xl">
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">About the Election Management System</h1>
                    <p class="mt-4 text-purple-100 text-lg md:text-xl">Empowering organizations to conduct secure, transparent, and efficient elections with confidence.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <span class="inline-flex items-center bg-white/10 border border-white/20 px-3 py-1 rounded-full text-sm">
                            <i class="fas fa-lock mr-2"></i>Security-First
                        </span>
                        <span class="inline-flex items-center bg-white/10 border border-white/20 px-3 py-1 rounded-full text-sm">
                            <i class="fas fa-chart-bar mr-2"></i>Real-time Results
                        </span>
                        <span class="inline-flex items-center bg-white/10 border border-white/20 px-3 py-1 rounded-full text-sm">
                            <i class="fas fa-users mr-2"></i>Multi-role Access
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Overview -->
        <section class="container mx-auto px-6 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-purple-800">Our Mission</h2>
                    <p class="mt-4 text-gray-700 leading-relaxed">The Election Management System (EMS) simplifies the entire lifecycle of elections—from setup and candidate registration to voter participation and final results. Designed for universities, associations, cooperatives, and enterprises, EMS brings reliability and accountability to democratic processes.</p>
                    <ul class="mt-6 space-y-3 text-gray-700">
                        <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>Role-based access for Admins, Candidates, and Voters</li>
                        <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>Instant, visual results powered by detailed analytics</li>
                        <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>Transparent, auditable actions for trust and compliance</li>
                        <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>Responsive design for seamless access on any device</li>
                    </ul>
                </div>
                <div>
                    <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-purple-100">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                            <div>
                                <div class="text-3xl font-extrabold text-purple-700">50+</div>
                                <div class="text-sm text-gray-500">Elections Managed</div>
                            </div>
                            <div>
                                <div class="text-3xl font-extrabold text-purple-700">10k+</div>
                                <div class="text-sm text-gray-500">Votes Recorded</div>
                            </div>
                            <div>
                                <div class="text-3xl font-extrabold text-purple-700">99.9%</div>
                                <div class="text-sm text-gray-500">Uptime</div>
                            </div>
                            <div>
                                <div class="text-3xl font-extrabold text-purple-700">24/7</div>
                                <div class="text-sm text-gray-500">Support</div>
                            </div>
                        </div>
                        <div class="mt-6 text-sm text-gray-500">Numbers shown are representative for demonstration purposes.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="bg-white border-t border-gray-100">
            <div class="container mx-auto px-6 py-16">
                <h2 class="text-3xl font-bold text-center text-purple-800">What Makes EMS Stand Out</h2>
                <p class="text-center text-gray-600 mt-2 max-w-2xl mx-auto">A modern toolkit for building trust and delivering results.</p>
                <div class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-shield-alt"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Secure by Design</h3>
                        <p class="text-gray-700 mt-2">Role-based authentication, protected endpoints, and auditable actions keep your elections safe.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-chart-pie"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Insightful Analytics</h3>
                        <p class="text-gray-700 mt-2">Visualize results using clear charts and breakdowns that enhance understanding and trust.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-mobile-alt"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Responsive Experience</h3>
                        <p class="text-gray-700 mt-2">Optimized for all devices so everyone can participate from anywhere.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-cogs"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Flexible Configuration</h3>
                        <p class="text-gray-700 mt-2">Fine-tuned controls let you adapt elections to your organization’s needs.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-user-shield"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Privacy Respect</h3>
                        <p class="text-gray-700 mt-2">Designed to respect voter privacy and confidentiality at every step.</p>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-purple-50">
                        <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center"><i class="fas fa-headset"></i></div>
                        <h3 class="mt-4 font-semibold text-purple-900">Dedicated Support</h3>
                        <p class="text-gray-700 mt-2">We’re here to help—from onboarding to successful election completion.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="container mx-auto px-6 py-16">
            <div class="bg-gradient-to-r from-purple-700 to-purple-800 rounded-2xl p-8 md:p-10 text-white flex flex-col md:flex-row items-center justify-between">
                <div class="max-w-2xl">
                    <h3 class="text-2xl md:text-3xl font-bold">Ready to run your next election with confidence?</h3>
                    <p class="text-purple-100 mt-2">Get started by creating an account or contacting our team for a tailored walkthrough.</p>
                </div>
                <div class="mt-6 md:mt-0 flex gap-3">
                    <a href="/EMS2/public/register.php" class="bg-white text-purple-800 px-6 py-3 rounded-lg font-semibold shadow hover:bg-purple-50">Get Started</a>
                    <a href="/EMS2/public/contact.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-purple-500">Contact Us</a>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
