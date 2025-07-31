<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Execute Config Script';
$pdo = get_pdo();
$id = $_GET['id'] ?? null;
if (!$id) { echo 'No script specified.'; exit; }
$stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ? AND type = ?');
$stmt->execute([$id, 'config_script']);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) { echo 'Script not found or not allowed.'; exit; }
$output = '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: Only allow execution if you trust your environment!
    $script = $item['script'];
    if ($script) {
        $output = shell_exec($script . ' 2>&1');
    } else {
        $output = 'No script defined.';
    }
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 700px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Execute Config Script: <?= htmlspecialchars($item['label']) ?></h2>
    <div class="alert alert-warning">Warning: This will execute a bash script on the server. Only use this if you trust the script and your environment.</div>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Script to execute:</label>
        <pre class="bg-light p-2 border rounded small">$ <?= htmlspecialchars($item['script']) ?></pre>
      </div>
      <button type="submit" class="btn btn-danger">Execute Script</button>
    </form>
    <?php if ($output !== ''): ?>
      <div class="mt-4">
        <label class="form-label">Output:</label>
        <pre class="bg-light p-2 border rounded small"><?= htmlspecialchars($output) ?></pre>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 