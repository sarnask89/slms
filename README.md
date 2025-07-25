# sLMS - System zarządzania siecią lokalną

## 📋 Opis projektu

sLMS (System Local Management System) to kompleksowe rozwiązanie do zarządzania siecią lokalną, monitorowania urządzeń i integracji z systemami Cacti. System oferuje zaawansowane funkcje monitorowania SNMP, zarządzania klientami, generowania raportów i administracji systemem.

## ✨ Główne funkcje

### 🔐 System uwierzytelniania
- Bezpieczne logowanie z sesjami
- Role użytkowników (admin, manager, user, viewer)
- Zarządzanie uprawnieniami modułowymi
- Dziennik aktywności użytkowników
- Profil użytkownika z możliwością zmiany hasła

### 📊 Monitorowanie sieci
- Integracja z Cacti
- Monitorowanie SNMP w czasie rzeczywistym
- Wykrywanie urządzeń MNDP
- Statystyki interfejsów sieciowych
- Monitoring kolejek

### 👥 Zarządzanie klientami
- Dodawanie i edycja klientów
- Zarządzanie urządzeniami klientów
- Pakiety internetowe i usługi
- System faktur i płatności

### 🌐 Zarządzanie sieciami
- Konfiguracja sieci DHCP
- Import klientów DHCP
- Zarządzanie VLAN-ami
- Monitoring dostępności

### 🛠️ Administracja systemu
- Edytor menu z hierarchiczną strukturą
- Konfiguracja motywów
- Zarządzanie użytkownikami
- Konsola SQL
- Backup systemu

## 🚀 Instalacja

### Wymagania systemowe
- PHP 8.0 lub nowszy
- MySQL 5.7+ lub MariaDB 10.2+
- Apache/Nginx
- Rozszerzenia PHP: PDO, PDO_MySQL, SNMP, cURL, JSON

### Krok 1: Klonowanie repozytorium
```bash
git clone https://github.com/sarnask89/slms.git
cd slms
```

### Krok 2: Konfiguracja bazy danych
```bash
# Skopiuj plik konfiguracyjny
cp config.example.php config.php

# Edytuj config.php i ustaw dane dostępowe do bazy danych
nano config.php
```

### Krok 3: Utworzenie bazy danych
```sql
CREATE DATABASE slmsdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'slms'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON slmsdb.* TO 'slms'@'localhost';
FLUSH PRIVILEGES;
```

### Krok 4: Inicjalizacja systemu
```bash
# Uruchom skrypt inicjalizacyjny
php modules/setup_auth_tables.php
```

### Krok 5: Uruchomienie serwera deweloperskiego
```bash
# Uruchom lokalny serwer PHP
./run_local_server.sh
```

## 🔧 Konfiguracja

### Plik config.php
Główne ustawienia systemu znajdują się w pliku `config.php`:

```php
$db_host = 'localhost';
$db_name = 'slmsdb';
$db_user = 'slms';
$db_pass = 'your_password';
$db_charset = 'utf8mb4';
```

### Integracja z Cacti
Aby skonfigurować integrację z Cacti, dodaj do `config.php`:

```php
$cacti_url = 'http://your-cacti-server:8081';
$cacti_username = 'admin';
$cacti_password = 'admin';
```

## 📖 Użycie

### Pierwsze uruchomienie
1. Otwórz przeglądarkę i przejdź do `http://localhost:8000`
2. System automatycznie przekieruje Cię do konfiguracji pierwszego użytkownika
3. Utwórz konto administratora
4. Zaloguj się i rozpocznij konfigurację systemu

### Dodawanie urządzeń
1. Przejdź do **Administracja** → **Zarządzanie Urządzeniami**
2. Kliknij **Dodaj Urządzenie**
3. Wprowadź dane urządzenia (IP, community SNMP)
4. Przetestuj połączenie SNMP
5. Zapisz urządzenie

### Monitorowanie SNMP
1. Przejdź do **SNMP i Monitoring Sieci**
2. Użyj **SNMP Monitoring** do sprawdzenia statusu urządzeń
3. **SNMP Graphing** do generowania wykresów
4. **Interface Monitoring** do monitorowania interfejsów

## 🏗️ Struktura projektu

```
slms/
├── assets/                 # Pliki CSS, JS, obrazy
├── docs/                   # Dokumentacja
├── modules/                # Moduły systemu
│   ├── helpers/           # Funkcje pomocnicze
│   ├── auth_helper.php    # System uwierzytelniania
│   ├── login.php          # Strona logowania
│   ├── user_management.php # Zarządzanie użytkownikami
│   └── ...                # Inne moduły
├── partials/              # Szablony częściowe
├── config.php             # Konfiguracja główna
├── config.example.php     # Przykład konfiguracji
├── index.php              # Strona główna
├── admin_menu.php         # Panel administracyjny
└── README.md              # Ten plik
```

## 🔒 Bezpieczeństwo

### Funkcje bezpieczeństwa
- Hasła hashowane z użyciem `password_hash()`
- Sesje PHP z automatycznym wygaśnięciem
- Walidacja danych wejściowych
- Ochrona przed SQL injection
- Kontrola dostępu oparta na rolach

### Najlepsze praktyki
- Zmień domyślne hasło administratora
- Używaj silnych haseł
- Regularnie aktualizuj system
- Twórz kopie zapasowe bazy danych
- Monitoruj dziennik aktywności

## 🐛 Rozwiązywanie problemów

### Problem z połączeniem SNMP
```bash
# Sprawdź czy rozszerzenie SNMP jest zainstalowane
php -m | grep snmp

# Przetestuj połączenie SNMP
snmpwalk -v2c -c public 192.168.1.1 .1.3.6.1.2.1.1.1.0
```

### Problem z bazą danych
```bash
# Sprawdź połączenie z bazą danych
php -r "require 'config.php'; get_pdo(); echo 'OK';"
```

### Problem z uprawnieniami
```bash
# Ustaw odpowiednie uprawnienia
chmod 755 /var/www/html/slms
chown -R www-data:www-data /var/www/html/slms
```

## 🤝 Współpraca

### Zgłaszanie błędów
1. Sprawdź czy problem nie został już zgłoszony
2. Utwórz nowy issue z opisem problemu
3. Dołącz logi błędów i informacje o środowisku

### Proponowanie zmian
1. Utwórz fork repozytorium
2. Utwórz branch dla nowej funkcjonalności
3. Wprowadź zmiany i przetestuj
4. Utwórz Pull Request

## 📄 Licencja

Ten projekt jest dostępny na licencji MIT. Zobacz plik `LICENSE` dla szczegółów.

## 👨‍💻 Autor

**sarnask89** - [GitHub](https://github.com/sarnask89)

## 🙏 Podziękowania

- Zespół Cacti za wspaniały system monitorowania
- Społeczność PHP za doskonałe narzędzia
- Wszystkim kontrybutorom projektu

---

**sLMS** - Profesjonalne zarządzanie siecią lokalną 🚀# slms
