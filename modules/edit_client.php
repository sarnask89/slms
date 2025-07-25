<?php
require_once __DIR__ . "/../config.php";
$pageTitle = "Edytuj Klienta";
$pdo = get_pdo();
$id = $_GET["id"] ?? null;
if (!$id) { header("Location: clients.php"); exit; }
// Fetch client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$client) { header("Location: clients.php"); exit; }
// Handle update
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $pdo->prepare("UPDATE clients SET name=?, first_name=?, last_name=?, pesel=?, phone=?, email=?, address=?, city=?, postal_code=?, company_name=?, nip=?, notes=? WHERE id=?");
    $stmt->execute([
        $_POST["name"],
        $_POST["first_name"] ?? null,
        $_POST["last_name"] ?? null,
        $_POST["pesel"] ?? null,
        $_POST["phone"] ?? null,
        $_POST["email"] ?? null,
        $_POST["address"] ?? null,
        $_POST["city"] ?? null,
        $_POST["postal_code"] ?? null,
        $_POST["company_name"] ?? null,
        $_POST["nip"] ?? null,
        $_POST["notes"] ?? null,
        $id
    ]);
    header("Location: clients.php");
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
            <input type="text" class="form-control" id="name" name="name" placeholder="Nazwa" required value="<?= htmlspecialchars($client["name"]) ?>">
            <label for="name">Nazwa *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Imię" required value="<?= htmlspecialchars($client["first_name"]) ?>">
            <label for="first_name">Imię *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Nazwisko" required value="<?= htmlspecialchars($client["last_name"]) ?>">
            <label for="last_name">Nazwisko *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="pesel" name="pesel" placeholder="PESEL" value="<?= htmlspecialchars($client["pesel"] ?? "") ?>">
            <label for="pesel">PESEL</label>
          </div>
          <div class="form-floating mb-3">
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Telefon" value="<?= htmlspecialchars($client["phone"] ?? "") ?>">
            <label for="phone">Telefon</label>
          </div>
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($client["email"] ?? "") ?>">
            <label for="email">E-mail</label>
          </div>
        </div>
        
        <div class="col-md-6">
          <h5 class="mb-3">Adres</h5>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="address" name="address" placeholder="Adres" style="height: 100px"><?= htmlspecialchars($client["address"] ?? "") ?></textarea>
            <label for="address">Adres</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="city" name="city" placeholder="Miasto" value="<?= htmlspecialchars($client["city"] ?? "") ?>">
            <label for="city">Miasto</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Kod pocztowy" value="<?= htmlspecialchars($client["postal_code"] ?? "") ?>">
            <label for="postal_code">Kod pocztowy</label>
          </div>
          
          <h5 class="mb-3 mt-4">Dane firmy</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Nazwa firmy" value="<?= htmlspecialchars($client["company_name"] ?? "") ?>">
            <label for="company_name">Nazwa firmy</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="nip" name="nip" placeholder="NIP" value="<?= htmlspecialchars($client["nip"] ?? "") ?>">
            <label for="nip">NIP</label>
          </div>
        </div>
      </div>
      
      <div class="row mt-4">
        <div class="col-12">
          <h5 class="mb-3">Dodatkowe informacje</h5>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="notes" name="notes" placeholder="Notatki" style="height: 100px"><?= htmlspecialchars($client["notes"] ?? "") ?></textarea>
            <label for="notes">Notatki</label>
          </div>
        </div>
      </div>
      
      <div class="mt-4">
        <button type="submit" class="btn lms-btn-accent">Zapisz zmiany</button>
        <a href="<?= base_url("modules/clients.php") ?>" class="btn btn-secondary ms-2">Anuluj</a>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/../partials/layout.php"; 
?>
