<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Management System - Secure, Transparent, Efficient</title>
    <meta name="description" content="Professional Election Management System for secure, transparent, and efficient election processes. Manage candidates, voters, and results with confidence.">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 50%, #6D28D9 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(139, 92, 246, 0.2); }
        .feature-icon { background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); }
        .stats-counter { font-weight: 700; font-size: 2.5rem; color: #6D28D9; }
        .image-loading { opacity: 0.7; filter: blur(1px); }
        .image-loaded { opacity: 1; filter: none; transition: all 0.3s ease; }
        .image-error { border: 2px solid #ef4444; opacity: 0.8; }
        .candidate-card { 
            background: linear-gradient(145deg, #ffffff 0%, #faf5ff 100%);
            border: 1px solid #e9d5ff;
        }
        .candidate-card:hover {
            background: linear-gradient(145deg, #faf5ff 0%, #ffffff 100%);
            border-color: #8B5CF6;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.15);
        }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .section-divider {
            background: linear-gradient(90deg, transparent, #8B5CF6, transparent);
            height: 1px;
            margin: 4rem 0;
        }
        .purple-gradient-text {
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .purple-shadow { box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3); }
        
        /* Enhanced contrast for better readability */
        .bg-white .text-purple-600 { color: #7C3AED !important; }
        .bg-white .text-purple-700 { color: #6D28D9 !important; }
        .bg-white .text-purple-800 { color: #5B21B6 !important; }
        .bg-gray-50 .text-purple-600 { color: #7C3AED !important; }
        .bg-gray-50 .text-purple-700 { color: #6D28D9 !important; }
        .bg-gray-50 .text-purple-800 { color: #5B21B6 !important; }
        
        /* Ensure proper contrast on light backgrounds */
        .light-bg-text { color: #5B21B6 !important; }
        .medium-bg-text { color: #6D28D9 !important; }
        .dark-bg-text { color: #ffffff !important; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-4xl mx-auto leading-relaxed">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    Secure Election
                    <span class="text-purple-200">Management</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-gray-100 leading-relaxed">
                    Empowering democracy through transparent, efficient, and secure election processes. 
                    Built for organizations that value integrity and trust.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/EMS2/public/login.php" class="bg-white text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-50 transition duration-300 purple-shadow">
                        <i class="fas fa-sign-in-alt mr-2"></i>Access System
                    </a>
                    <a href="#features" class="border-2 border-purple-200 text-purple-100 px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-200 hover:text-purple-800 transition duration-300">
                        <i class="fas fa-info-circle mr-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-purple-300 opacity-20 rounded-full pulse-animation"></div>
        <div class="absolute bottom-20 right-10 w-32 h-32 bg-purple-200 opacity-15 rounded-full pulse-animation" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-purple-400 opacity-10 rounded-full pulse-animation" style="animation-delay: 0.5s;"></div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <?php
                require_once '../includes/db.php';
                
                // Get statistics
                $total_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'] ?? 0;
                $total_voters = $conn->query("SELECT COUNT(*) as count FROM voters")->fetch_assoc()['count'] ?? 0;
                $total_elections = $conn->query("SELECT COUNT(*) as count FROM elections")->fetch_assoc()['count'] ?? 0;
                $total_votes = $conn->query("SELECT COUNT(*) as count FROM votes")->fetch_assoc()['count'] ?? 0;
                ?>
                <div class="card-hover p-6 bg-white rounded-xl shadow-lg border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <div class="stats-counter light-bg-text"><?= number_format($total_candidates) ?></div>
                    <p class="light-bg-text font-medium">Registered Candidates</p>
                </div>
                <div class="card-hover p-6 bg-white rounded-xl shadow-lg border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-check text-white text-2xl"></i>
                    </div>
                    <div class="stats-counter light-bg-text"><?= number_format($total_voters) ?></div>
                    <p class="light-bg-text font-medium">Eligible Voters</p>
                </div>
                <div class="card-hover p-6 bg-white rounded-xl shadow-lg border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-vote-yea text-white text-2xl"></i>
                    </div>
                    <div class="stats-counter light-bg-text"><?= number_format($total_elections) ?></div>
                    <p class="light-bg-text font-medium">Active Elections</p>
                </div>
                <div class="card-hover p-6 bg-white rounded-xl shadow-lg border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <div class="stats-counter light-bg-text"><?= number_format($total_votes) ?></div>
                    <p class="light-bg-text font-medium">Votes Cast</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold purple-gradient-text mb-4 leading-tight">Why Choose Our EMS?</h2>
                <p class="text-xl leading-relaxed medium-bg-text max-w-3xl mx-auto">
                    Built with cutting-edge technology and security best practices to ensure your elections are transparent, secure, and efficient.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Bank-Level Security</h3>
                    <p class="light-bg-text leading-relaxed">
                        Advanced encryption, secure authentication, and comprehensive audit trails ensure your election data is protected at all times.
                    </p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Complete Transparency</h3>
                    <p class="light-bg-text leading-relaxed">
                        Real-time results, detailed reporting, and comprehensive logs provide full visibility into every aspect of your election process.
                    </p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-rocket text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Lightning Fast</h3>
                    <p class="light-bg-text leading-relaxed">
                        Optimized performance ensures smooth voting experiences even during peak times, with instant result compilation and reporting.
                    </p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Mobile Responsive</h3>
                    <p class="light-bg-text leading-relaxed">
                        Access from any device - desktop, tablet, or mobile. Our responsive design ensures a seamless experience across all platforms.
                    </p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Role-Based Access</h3>
                    <p class="light-bg-text leading-relaxed">
                        Sophisticated user management with granular permissions for administrators, candidates, and voters ensures proper access control.
                    </p>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border border-purple-100">
                    <div class="feature-icon w-16 h-16 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold light-bg-text mb-4">Advanced Analytics</h3>
                    <p class="light-bg-text leading-relaxed">
                        Comprehensive reporting and analytics with visual charts, export capabilities, and detailed insights into voting patterns.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Candidates Section -->
    <?php
    require_once '../includes/image_utils.php';
    $latest = $conn->query("SELECT c.id, c.full_name, c.profile_image, c.party, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6");
    ?>
    
    <?php if ($latest && $latest->num_rows > 0): ?>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold purple-gradient-text mb-4">Featured Candidates</h2>
                <p class="text-xl light-bg-text max-w-3xl mx-auto">
                    Meet the candidates who are shaping the future. Learn about their backgrounds, platforms, and vision for change.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($c = $latest->fetch_assoc()): 
                    $img = getPublicCandidateImageUrl($c['profile_image']);
                ?>
                <div class="candidate-card rounded-2xl overflow-hidden shadow-lg card-hover">
                    <div class="relative">
                        <img src="<?= htmlspecialchars($img) ?>" 
                             alt="<?= htmlspecialchars($c['full_name']) ?>" 
                             class="w-full h-64 object-cover image-loading" 
                             loading="lazy"
                             onerror="this.onerror=null;this.src='/EMS2/assets/ems_intro.svg';this.classList.add('image-error');"
                             onload="this.classList.remove('image-loading');this.classList.add('image-loaded');">
                        
                        <div class="absolute top-4 left-4">
                            <span class="bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                <?= htmlspecialchars($c['election_title'] ?? 'Candidate') ?>
                            </span>
                        </div>
                        
                        <div class="absolute top-4 right-4">
                            <span class="bg-white bg-opacity-90 text-purple-600 px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                <i class="fas fa-star text-yellow-500"></i>
                                <?= number_format($c['votes'] ?? 0) ?>
                            </span>
                        </div>
                        
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-transparent to-transparent p-6">
                            <h3 class="text-white text-xl font-bold mb-1">
                                <?= htmlspecialchars($c['full_name']) ?>
                            </h3>
                            <?php if (!empty($c['party'])): ?>
                            <p class="text-gray-200 text-sm">
                                <?= htmlspecialchars($c['party']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="light-bg-text mb-4">
                            Running for <?= htmlspecialchars($c['election_title'] ?? 'Office') ?>. 
                            Committed to bringing positive change and representing the people's interests.
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($img) ?>" 
                                     alt="<?= htmlspecialchars($c['full_name']) ?>" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-purple-200"
                                     onerror="this.onerror=null;this.src='/EMS2/assets/ems_intro.svg';">
                                <div>
                                    <p class="font-medium light-bg-text"><?= htmlspecialchars($c['full_name']) ?></p>
                                    <p class="text-sm medium-bg-text">Candidate</p>
                                </div>
                            </div>
                            
                            <a href="/EMS2/public/login.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 text-sm font-medium">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="/EMS2/public/login.php" class="bg-purple-600 text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-700 transition duration-300 purple-shadow">
                    <i class="fas fa-users mr-2"></i>View All Candidates
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="py-20 hero-gradient text-white relative overflow-hidden mb-20">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl font-bold mb-6 leading-tight">Ready to Get Started?</h2>
            <p class="text-xl leading-relaxed mb-8 max-w-2xl mx-auto text-purple-100">
                Join thousands of organizations worldwide who trust our Election Management System 
                for their democratic processes.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/EMS2/public/login.php" class="bg-white text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-50 transition duration-300 purple-shadow">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to System
                </a>
                <a href="/EMS2/public/register.php" class="border-2 border-purple-200 text-purple-100 px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-200 hover:text-purple-800 transition duration-300">
                    <i class="fas fa-user-plus mr-2"></i>Register Account
                </a>
            </div>
        </div>
    </section>

     <div>

     </div>

    <?php include '../includes/footer.php'; ?>
    
    <!-- Enhanced JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Image loading management
        const images = document.querySelectorAll('img');
        let loadedCount = 0;
        let errorCount = 0;
        
        images.forEach(function(img, index) {
            img.addEventListener('load', function() {
                loadedCount++;
                this.classList.remove('image-loading');
                this.classList.add('image-loaded');
            });
            
            img.addEventListener('error', function() {
                errorCount++;
                this.classList.add('image-error');
                console.warn(`Image failed to load: ${this.src}`);
            });
        });

        // Animate stats counters
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.textContent.replace(/,/g, ''));
                    animateCounter(counter, target);
                    observer.unobserve(counter);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stats-counter').forEach(counter => {
            observer.observe(counter);
        });

        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 30);
        }

        // Add loading states
        const cards = document.querySelectorAll('.card-hover');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        console.log('Professional EMS Home Page loaded successfully');
    });
    </script>
</body>
</html>