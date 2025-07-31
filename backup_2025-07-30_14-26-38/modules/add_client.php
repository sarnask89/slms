<?php
require_once __DIR__ . "/../config.php";
$pageTitle = "Dodaj Klienta";
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("INSERT INTO clients (name, first_name, last_name, pesel, phone, email, address, city, postal_code, company_name, nip, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
        "active"
    ]);
    header("Location: clients.php");
    exit;
}
ob_start();
?>
<div class="container">
  <div class="lms-card p-4 mt-4">
    <h2 class="lms-accent mb-4">Dodaj Klienta</h2>
    <form method="post">
      <div class="row">
        <div class="col-md-6">
          <h5 class="mb-3">Podstawowe informacje</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="name" name="name" placeholder="Nazwa" required>
            <label for="name">Nazwa *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Imię" required>
            <label for="first_name">Imię *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Nazwisko" required>
            <label for="last_name">Nazwisko *</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="pesel" name="pesel" placeholder="PESEL">
            <label for="pesel">PESEL</label>
          </div>
          <div class="form-floating mb-3">
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Telefon">
            <label for="phone">Telefon</label>
          </div>
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="E-mail">
            <label for="email">E-mail</label>
          </div>
        </div>
        
        <div class="col-md-6">
          <h5 class="mb-3">Adres</h5>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="address" name="address" placeholder="Adres" style="height: 100px"></textarea>
            <label for="address">Adres</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="city" name="city" placeholder="Miasto">
            <label for="city">Miasto</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Kod pocztowy">
            <label for="postal_code">Kod pocztowy</label>
          </div>
          
          <h5 class="mb-3 mt-4">Dane firmy</h5>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Nazwa firmy">
            <label for="company_name">Nazwa firmy</label>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="nip" name="nip" placeholder="NIP">
            <label for="nip">NIP</label>
          </div>
        </div>
      </div>
      
      <div class="row mt-4">
        <div class="col-12">
          <h5 class="mb-3">Dodatkowe informacje</h5>
          <div class="form-floating mb-3">
            <textarea class="form-control" id="notes" name="notes" placeholder="Notatki" style="height: 100px"></textarea>
            <label for="notes">Notatki</label>
          </div>
        </div>
      </div>
      
      <div class="mt-4">
        <button type="submit" class="btn lms-btn-accent">Dodaj Klienta</button>
        <a href="<?= base_url("modules/clients.php") ?>" class="btn btn-secondary ms-2">Anuluj</a>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . "/../partials/layout.php"; 
?>
