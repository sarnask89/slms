<?php
require_once 'module_loader.php';

$pageTitle = 'Edytuj Klienta';
$pdo = get_pdo();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: clients.php'); exit; }
// Fetch client
$stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$client) { header('Location: clients.php'); exit; }
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE clients SET name=?, altname=?, address=?, post_name=?, post_address=?, location_name=?, location_address=?, email=?, bankaccount=?, ten=?, ssn=?, additional_info=?, notes=?, documentmemo=?, contact_info=? WHERE id=?');
    $stmt->execute([
        $_POST['name'],
        $_POST['altname'] ?? null,
        $_POST['address'] ?? null,
        $_POST['post_name'] ?? null,
        $_POST['post_address'] ?? null,
        $_POST['location_name'] ?? null,
        $_POST['location_address'] ?? null,
        $_POST['email'] ?? null,
        $_POST['bankaccount'] ?? null,
        $_POST['ten'] ?? null,
        $_POST['ssn'] ?? null,
        $_POST['additional_info'] ?? null,
        $_POST['notes'] ?? null,
        $_POST['documentmemo'] ?? null,
        $_POST['contact_info'] ?? null,
        $id
    ]);
    header('Location: clients.php');
    exit;
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <h2 class="lms-accent mb-4">Edytuj Klienta</h2>
    <form method="post">
      <div class="row">
        <div class="col-md-6">
          <h5 class="mb-3">Podstawowe informacje</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="name" name="name" placeholder="Nazwa" required value="<?= htmlspecialchars($client['name']) ?>">
            <label for="name">Nazwa *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="altname" name="altname" placeholder="Alternatywna nazwa" value="<?= htmlspecialchars($client['altname'] ?? '') ?>">
            <label for="altname">Alternatywna nazwa</label>
          </div>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="address" name="address" placeholder="Adres" style="height: 100px"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
            <label for="address">Adres</label>
          </div>
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
            <label for="email">E-mail</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="contact_info" name="contact_info" placeholder="Informacje kontaktowe" value="<?= htmlspecialchars($client['contact_info'] ?? '') ?>">
            <label for="contact_info">Informacje kontaktowe</label>
          </div>
        </div>
        
        <div class="col-md-6">
          <h5 class="mb-3">Adres korespondencyjny</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="post_name" name="post_name" placeholder="Nazwa korespondencyjna" value="<?= htmlspecialchars($client['post_name'] ?? '') ?>">
            <label for="post_name">Nazwa korespondencyjna</label>
          </div>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="post_address" name="post_address" placeholder="Adres korespondencyjny" style="height: 100px"><?= htmlspecialchars($client['post_address'] ?? '') ?></textarea>
            <label for="post_address">Adres korespondencyjny</label>
          </div>
          
          <h5 class="mb-3 mt-4">Lokalizacja</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="location_name" name="location_name" placeholder="Nazwa lokalizacji" value="<?= htmlspecialchars($client['location_name'] ?? '') ?>">
            <label for="location_name">Nazwa lokalizacji</label>
          </div>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="location_address" name="location_address" placeholder="Adres lokalizacyjny" style="height: 100px"><?= htmlspecialchars($client['location_address'] ?? '') ?></textarea>
            <label for="location_address">Adres lokalizacyjny</label>
          </div>
        </div>
      </div>
      
      <div class="row mt-4">
        <div class="col-md-6">
          <h5 class="mb-3">Dane finansowe</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="bankaccount" name="bankaccount" placeholder="Rachunek bankowy" value="<?= htmlspecialchars($client['bankaccount'] ?? '') ?>">
            <label for="bankaccount">Rachunek bankowy</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="ten" name="ten" placeholder="NIP" value="<?= htmlspecialchars($client['ten'] ?? '') ?>">
            <label for="ten">NIP</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="ssn" name="ssn" placeholder="PESEL" value="<?= htmlspecialchars($client['ssn'] ?? '') ?>">
            <label for="ssn">PESEL</label>
          </div>
        </div>
        
        <div class="col-md-6">
          <h5 class="mb-3">Dodatkowe informacje</h5>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="additional_info" name="additional_info" placeholder="Informacje dodatkowe" style="height: 100px"><?= htmlspecialchars($client['additional_info'] ?? '') ?></textarea>
            <label for="additional_info">Informacje dodatkowe</label>
          </div>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="notes" name="notes" placeholder="Notatki" style="height: 100px"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
            <label for="notes">Notatki</label>
          </div>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="documentmemo" name="documentmemo" placeholder="Notatka na dokumentach" style="height: 100px"><?= htmlspecialchars($client['documentmemo'] ?? '') ?></textarea>
            <label for="documentmemo">Notatka na dokumentach</label>
          </div>
        </div>
      </div>
      
      <div class="mt-4">
        <button type="submit" class="btn lms-btn-accent">Zapisz zmiany</button>
        <a href="<?= base_url('modules/clients.php') ?>" class="btn btn-secondary ms-2">Anuluj</a>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../partials/layout.php'; 