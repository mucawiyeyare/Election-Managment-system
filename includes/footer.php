<footer class="bg-purple-800 text-purple-100 mt-auto leading-relaxed">
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Company Info -->
            <div class="md:col-span-2">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-vote-yea text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Election Management System</h3>
                </div>
                <p class="text-purple-200 mb-6 leading-relaxed max-w-md">
                    Empowering democracy through secure, transparent, and efficient election management. 
                    Trusted by organizations worldwide for their democratic processes.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 bg-purple-700 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                        <i class="fab fa-facebook-f text-sm text-white"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-purple-700 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                        <i class="fab fa-twitter text-sm text-white"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-purple-700 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                        <i class="fab fa-linkedin-in text-sm text-white"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-purple-700 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                        <i class="fab fa-github text-sm text-white"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
                <ul class="space-y-3">
                    <li><a href="/EMS2/public/index.php" class="transition duration-300 flex items-center hover:text-white">
                        <i class="fas fa-home w-4 mr-2"></i>Home
                    </a></li>
                    <li><a href="/EMS2/public/login.php" class="transition duration-300 flex items-center hover:text-white">
                        <i class="fas fa-sign-in-alt w-4 mr-2"></i>Login
                    </a></li>
                    <li><a href="/EMS2/public/register.php" class="transition duration-300 flex items-center hover:text-white">
                        <i class="fas fa-user-plus w-4 mr-2"></i>Register
                    </a></li>
                    <li><a href="/EMS2/public/about.php" class="transition duration-300 flex items-center hover:text-white">
                        <i class="fas fa-info-circle w-4 mr-2"></i>About Us
                    </a></li>
                    <li><a href="/EMS2/public/contact.php" class="transition duration-300 flex items-center hover:text-white">
                        <i class="fas fa-envelope w-4 mr-2"></i>Contact
                    </a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Get in Touch</h4>
                <ul class="space-y-3">
                    <li class="flex items-center">
                        <i class="fas fa-envelope w-4 mr-3 text-purple-200"></i>
                        <span class="text-purple-100">support@ems-system.com</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-phone w-4 mr-3 text-purple-200"></i>
                        <span class="text-purple-100">+1 (555) 123-4567</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-clock w-4 mr-3 text-purple-200"></i>
                        <span class="text-purple-100">24/7 Support Available</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-shield-alt w-4 mr-3 text-purple-200"></i>
                        <span class="text-purple-100">Secure & Reliable</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-purple-700 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-purple-200 mb-4 md:mb-0">
                    © <?php echo date('Y'); ?> Election Management System. All rights reserved.
                </div>
                <div class="flex items-center space-x-6 text-sm text-purple-200">
                    <a href="#" class="transition duration-300 hover:text-white">Privacy Policy</a>
                    <a href="#" class="transition duration-300 hover:text-white">Terms of Service</a>
                    <a href="#" class="transition duration-300 hover:text-white">Security</a>
                    <span class="flex items-center">
                        <i class="fas fa-heart text-red-500 mx-1"></i>
                        Built with care
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>
