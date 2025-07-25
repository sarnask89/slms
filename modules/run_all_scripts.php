<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Run All Menu Scripts';
$pdo = get_pdo();
$outputs = [];
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT label, script FROM menu_items WHERE script IS NOT NULL AND TRIM(script) != "" ORDER BY position ASC, id ASC');
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $out = shell_exec($item['script'] . ' 2>&1');
        $outputs[] = [
            'label' => $item['label'],
            'script' => $item['script'],
            'output' => $out
        ];
    }
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4" style="max-width: 800px; margin: 0 auto;">
    <h2 class="lms-accent mb-4">Run All Menu Scripts</h2>
    <div class="alert alert-warning">Warning: This will execute <b>all bash scripts</b> defined in the menu items on the server. Only use this if you trust all scripts and your environment.</div>
    <form method="post">
      <button type="submit" class="btn btn-danger mb-3">Run All Scripts</button>
    </form>
    <?php if ($outputs): ?>
      <div class="mt-4">
        <h4>Script Outputs</h4>
        <?php foreach ($outputs as $item): ?>
          <div class="mb-4">
            <div class="fw-bold mb-1">Menu Item: <?= htmlspecialchars($item['label']) ?></div>
            <div class="small text-muted mb-1">$ <?= htmlspecialchars($item['script']) ?></div>
            <pre class="bg-light p-2 border rounded small"><?= htmlspecialchars($item['output']) ?></pre>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 