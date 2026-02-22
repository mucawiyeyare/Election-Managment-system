<?php
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/db.php';
    
    // Basic sanitization
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if ($name === '') { $errors['name'] = 'Name is required.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
    if ($message === '') { $errors['message'] = 'Message is required.'; }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        $stmt->execute();
        $stmt->close();
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact Us | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
    <?php include '../includes/navbar.php'; ?>

    <main class="flex-1">
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-700 via-purple-600 to-purple-800 opacity-95"></div>
            <div class="relative container mx-auto px-6 py-16 text-white">
                <div class="max-w-3xl">
                    <h1 class="text-4xl md:text-5xl font-extrabold">Contact Us</h1>
                    <p class="mt-3 text-purple-100 text-lg">Have questions or need a demo? Our team is here to help you make your next election a success.</p>
                </div>
            </div>
        </section>

        <!-- Contact Content -->
        <section class="container mx-auto px-6 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Contact Info Cards -->
                <div class="space-y-6">
                    <div class="p-6 rounded-xl border border-purple-100 bg-white">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center mr-4">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-purple-900">Email</div>
                                <div class="text-gray-600">support@ems-system.com</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-white">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center mr-4">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-purple-900">Phone</div>
                                <div class="text-gray-600">+1 (555) 123-4567</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 rounded-xl border border-purple-100 bg-white">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center mr-4">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-purple-900">Hours</div>
                                <div class="text-gray-600">24/7 Support Available</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-purple-100">
                        <?php if ($success): ?>
                            <div class="bg-green-100 text-green-800 p-4 rounded mb-6 flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Thank you! Your message has been received. We will get back to you shortly.</span>
                            </div>
                        <?php endif; ?>

                        <h2 class="text-2xl font-bold text-purple-900">Send us a message</h2>
                        <p class="text-gray-600 mt-1">Fill out the form and we’ll be in touch as soon as possible.</p>

                        <form method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>" class="mt-1 w-full border border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg p-3 outline-none" required />
                                <?php if (!empty($errors['name'])): ?><p class="text-red-600 text-sm mt-1"><?php echo $errors['name']; ?></p><?php endif; ?>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" class="mt-1 w-full border border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg p-3 outline-none" required />
                                <?php if (!empty($errors['email'])): ?><p class="text-red-600 text-sm mt-1"><?php echo $errors['email']; ?></p><?php endif; ?>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <input type="text" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? 'General Inquiry') ?>" class="mt-1 w-full border border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg p-3 outline-none" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea name="message" rows="6" class="mt-1 w-full border border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-lg p-3 outline-none" required><?php echo htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                <?php if (!empty($errors['message'])): ?><p class="text-red-600 text-sm mt-1"><?php echo $errors['message']; ?></p><?php endif; ?>
                            </div>
                            <div class="md:col-span-2 flex items-center justify-between">
                                <p class="text-xs text-gray-500">By submitting, you agree to our terms and privacy policy.</p>
                                <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-3 rounded-lg shadow font-semibold">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="container mx-auto px-6 pb-16">
            <div class="bg-purple-50 border border-purple-100 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between">
                <div class="max-w-2xl">
                    <h3 class="text-xl md:text-2xl font-bold text-purple-900">Prefer a direct conversation?</h3>
                    <p class="text-gray-700 mt-1">Schedule a quick call with our team to explore how EMS fits your needs.</p>
                </div>
                <a href="mailto:support@ems-system.com" class="mt-4 md:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold shadow">Email Us</a>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
