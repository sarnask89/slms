<?php
// sql_console.php
// Simple SQL query runner for sLMS database

require_once __DIR__ . '/config.php';

$admin_password = 'letmein'; // Change this to something secure!
$auth = false;
$error = '';
$result = null;
$columns = [];
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = trim($_POST['query'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($password !== $admin_password) {
        $error = 'Nieprawidłowe hasło administratora.';
    } elseif ($query) {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query($query);
            if (preg_match('/^\s*SELECT/i', $query)) {
                $result = $stmt->fetchAll();
                if ($result && count($result) > 0) {
                    $columns = array_keys($result[0]);
                }
            } else {
                $result = $stmt->rowCount();
            }
            $auth = true;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'Proszę wprowadzić zapytanie SQL.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konsola SQL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body { padding-top: 40px; }</style>
</head>
<body>
<div class="container">
  <h1 class="mb-4">Konsola SQL</h1>
  <form method="post" class="mb-4">
    <div class="mb-3">
      <label for="password" class="form-label">Hasło Administratora</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
      <label for="query" class="form-label">Zapytanie SQL</label>
      <div class="input-group mb-2">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="presetDropdown">Przykłady</button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item preset-query" href="#" data-query="SELECT * FROM users;">SELECT * FROM users;</a></li>
          <li><a class="dropdown-item preset-query" href="#" data-query="SELECT * FROM clients;">SELECT * FROM clients;</a></li>
          <li><a class="dropdown-item preset-query" href="#" data-query="SELECT * FROM devices;">SELECT * FROM devices;</a></li>
          <li><a class="dropdown-item preset-query" href="#" data-query="SELECT * FROM invoices;">SELECT * FROM invoices;</a></li>
          <li><a class="dropdown-item preset-query" href="#" data-query="SHOW TABLES;">SHOW TABLES;</a></li>
        </ul>
        <textarea class="form-control" id="query" name="query" rows="4" required><?= htmlspecialchars($query) ?></textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Wykonaj Zapytanie</button>
  </form>
  <?php if ($error): ?>
    <div class="alert alert-danger">Błąd: <?= htmlspecialchars($error) ?></div>
  <?php elseif ($auth): ?>
    <?php if (is_array($result)): ?>
      <div class="alert alert-success">Zapytanie wykonane pomyślnie. Zwrócono <?= count($result) ?> wiersz(y).</div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <?php foreach ($columns as $col): ?>
                <th><?= htmlspecialchars($col) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($result as $row): ?>
              <tr>
                <?php foreach ($columns as $col): ?>
                  <td><?= htmlspecialchars($row[$col]) ?></td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-success">Zapytanie wykonane pomyślnie. Dotknięto <?= $result ?> wiersz(y).</div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preset query injection
const presetLinks = document.querySelectorAll('.preset-query');
const queryTextarea = document.getElementById('query');
presetLinks.forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    queryTextarea.value = this.getAttribute('data-query');
  });
});
</script>
</body>
</html> 