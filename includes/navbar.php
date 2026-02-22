<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="/EMS2/public/index.php" class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg flex items-center justify-center">
                    <i class="fas fa-vote-yea text-white text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-bold text-purple-900">EMS</span>
                    <div class="text-xs text-purple-700 -mt-1">Election Management</div>
                </div>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/EMS2/public/index.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 relative group">
                    Home
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-purple-600 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#features" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 relative group">
                    Features
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-purple-600 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/EMS2/public/about.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 relative group">
                    About
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-purple-600 group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="/EMS2/public/contact.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 relative group">
                    Contact
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-purple-600 group-hover:w-full transition-all duration-300"></span>
                </a>
            </div>
            
            <!-- Action Buttons -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="/EMS2/public/login.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300">
                    <i class="fas fa-sign-in-alt mr-1"></i>Login
                </a>
                <a href="/EMS2/public/register.php" class="bg-purple-600 text-white px-6 py-2 rounded-full hover:bg-purple-700 transition duration-300 font-medium shadow-lg">
                    <i class="fas fa-user-plus mr-1"></i>Register
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-purple-800 hover:text-purple-900 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="md:hidden hidden border-t border-purple-200 py-4">
            <div class="flex flex-col space-y-4">
                <a href="/EMS2/public/index.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 py-2">
                    <i class="fas fa-home w-5 mr-2"></i>Home
                </a>
                <a href="#features" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 py-2">
                    <i class="fas fa-star w-5 mr-2"></i>Features
                </a>
                <a href="/EMS2/public/about.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 py-2">
                    <i class="fas fa-info-circle w-5 mr-2"></i>About
                </a>
                <a href="/EMS2/public/contact.php" class="text-purple-800 hover:text-purple-900 font-medium transition duration-300 py-2">
                    <i class="fas fa-envelope w-5 mr-2"></i>Contact
                </a>
                <div class="border-t border-purple-200 pt-4 mt-4">
                    <a href="/EMS2/public/login.php" class="block text-purple-800 hover:text-purple-900 font-medium transition duration-300 py-2">
                        <i class="fas fa-sign-in-alt w-5 mr-2"></i>Login
                    </a>
                    <a href="/EMS2/public/register.php" class="block bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-300 font-medium mt-2 text-center">
                        <i class="fas fa-user-plus mr-1"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                const icon = mobileMenuButton.querySelector('i');
                if (mobileMenu.classList.contains('hidden')) {
                    icon.className = 'fas fa-bars text-xl';
                } else {
                    icon.className = 'fas fa-times text-xl';
                }
            });
        }
    });
    </script>
</nav> 