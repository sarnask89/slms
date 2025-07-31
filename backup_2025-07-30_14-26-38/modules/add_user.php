<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Add User';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['email'],
        $_POST['role']
    ]);
    header('Location: users.php');
    exit;
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 600px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Add User</h2>
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
        <label for="username">Username</label>
      </div>
      <div class="form-floating mb-3">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        <label for="password">Password</label>
      </div>
      <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="Email">
        <label for="email">Email</label>
      </div>
      <div class="form-floating mb-3">
        <input type="text" class="form-control" id="role" name="role" placeholder="Role">
        <label for="role">Role</label>
      </div>
      <button type="submit" class="btn lms-btn-accent w-100">Add User</button>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 