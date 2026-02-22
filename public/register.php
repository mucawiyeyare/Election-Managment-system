<?php
require_once '../includes/db.php';
$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($role === 'voter') {
        $gender = $_POST['gender'];
        $address = trim($_POST['address']);
    }
    if ($role === 'candidate') {
        $manifesto = trim($_POST['manifesto']);
        $profile_image = '';
    }
    // Check if username exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = 'Username already exists.';
    } else {
        // Insert into users
        $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $password, $role);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            if ($role === 'voter') {
                $stmt2 = $conn->prepare('INSERT INTO voters (user_id, full_name, email, phone, gender, address) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt2->bind_param('isssss', $user_id, $full_name, $email, $phone, $gender, $address);
                $stmt2->execute();
                $stmt2->close();
            } elseif ($role === 'candidate') {
                $stmt2 = $conn->prepare('INSERT INTO candidates (user_id, full_name, email, phone, manifesto, profile_image) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt2->bind_param('isssss', $user_id, $full_name, $email, $phone, $manifesto, $profile_image);
                $stmt2->execute();
                $stmt2->close();
            }
            $success = true;
        } else {
            $error = 'Registration failed.';
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
    function toggleRoleFields() {
        var role = document.getElementById('role').value;
        document.getElementById('voter-fields').style.display = (role === 'voter') ? 'block' : 'none';
        document.getElementById('candidate-fields').style.display = (role === 'candidate') ? 'block' : 'none';
    }
    </script>
</head>
<body class="min-h-screen flex flex-col bg-gray-50" onload="toggleRoleFields()">
    <?php include '../includes/navbar.php'; ?>
    <main class="flex-1">
    <div class="container mx-auto mt-10 p-6 bg-white rounded shadow max-w-lg">
        <h1 class="text-3xl font-bold mb-4">Register</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded mb-4"><?php echo $error; ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded mb-4">Registration successful! You can now <a href='login.php' class='underline'>login</a>.</div>
        <?php endif; ?>
        <form method="POST" class="space-y-4" enctype="multipart/form-data">
            <div>
                <label class="block text-gray-700">Role</label>
                <select name="role" id="role" onchange="toggleRoleFields()" required class="w-full border p-2 rounded">
                    <option value="voter">Voter</option>
                    <option value="candidate">Candidate</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700">Username</label>
                <input type="text" name="username" required class="w-full border p-2 rounded">
            </div>
            <div>
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" required class="w-full border p-2 rounded">
            </div>
            <div>
                <label class="block text-gray-700">Full Name</label>
                <input type="text" name="full_name" required class="w-full border p-2 rounded">
            </div>
            <div>
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" required class="w-full border p-2 rounded">
            </div>
            <div>
                <label class="block text-gray-700">Phone</label>
                <input type="text" name="phone" required class="w-full border p-2 rounded">
            </div>
            <div id="voter-fields">
                <div>
                    <label class="block text-gray-700">Gender</label>
                    <select name="gender" class="w-full border p-2 rounded">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700">Address</label>
                    <textarea name="address" class="w-full border p-2 rounded"></textarea>
                </div>
            </div>
            <div id="candidate-fields">
                <div>
                    <label class="block text-gray-700">Manifesto</label>
                    <textarea name="manifesto" class="w-full border p-2 rounded"></textarea>
                </div>
            </div>
            <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded">Register</button>
        </form>
    </div>
    <script>toggleRoleFields();</script>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html> 