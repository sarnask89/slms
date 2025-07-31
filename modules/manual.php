<?php
require_once 'module_loader.php';


$pageTitle = 'Podręcznik użytkownika';
$content = ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-book me-3"></i>Podręcznik użytkownika sLMS System</h1>
            <p class="lead text-muted">Kompletny przewodnik po funkcjach systemu sLMS z układem z ramkami</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="manualTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="bi bi-info-circle me-2"></i>Przegląd
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="frame-layout-tab" data-bs-toggle="tab" data-bs-target="#frame-layout" type="button" role="tab">
                <i class="bi bi-window-stack me-2"></i>Układ z ramkami
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="navigation-tab" data-bs-toggle="tab" data-bs-target="#navigation" type="button" role="tab">
                <i class="bi bi-compass me-2"></i>Nawigacja
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shortcuts-tab" data-bs-toggle="tab" data-bs-target="#shortcuts" type="button" role="tab">
                <i class="bi bi-keyboard me-2"></i>Skróty klawiszowe
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="troubleshooting-tab" data-bs-toggle="tab" data-bs-target="#troubleshooting" type="button" role="tab">
                <i class="bi bi-tools me-2"></i>Rozwiązywanie problemów
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button" role="tab">
                <i class="bi bi-question-circle me-2"></i>FAQ
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="manualTabContent">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-info-circle me-2"></i>Wprowadzenie do systemu sLMS</h4>
                        </div>
                        <div class="card-body">
                            <p>System sLMS (LAN Management System) to zaawansowane narzędzie do zarządzania sieciami komputerowymi, klientami, urządzeniami i usługami. System został wyposażony w nowoczesny układ z ramkami, który znacząco przyspiesza ładowanie stron i poprawia komfort użytkowania.</p>
                            
                            <h5>Główne funkcje systemu:</h5>
                            <ul>
                                <li><strong>Zarządzanie klientami</strong> - dodawanie, edycja i usuwanie klientów</li>
                                <li><strong>Zarządzanie urządzeniami</strong> - monitorowanie urządzeń sieciowych</li>
                                <li><strong>Zarządzanie sieciami</strong> - konfiguracja sieci IP i VLAN</li>
                                <li><strong>Zarządzanie usługami</strong> - pakiety internetowe i telewizyjne</li>
                                <li><strong>Faktury i płatności</strong> - system rozliczeniowy</li>
                                <li><strong>Raporty i analizy</strong> - szczegółowe raporty systemowe</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-speedometer2 me-2"></i>Korzyści z układu z ramkami</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-lightning-charge text-success fs-4 me-3"></i>
                                <div>
                                    <strong>Szybsze ładowanie</strong><br>
                                    <small class="text-muted">~80% szybsza nawigacja</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-memory text-primary fs-4 me-3"></i>
                                <div>
                                    <strong>Menu w pamięci</strong><br>
                                    <small class="text-muted">Nawigacja pozostaje załadowana</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-arrow-repeat text-info fs-4 me-3"></i>
                                <div>
                                    <strong>Automatyczne odświeżanie</strong><br>
                                    <small class="text-muted">Co 5 minut</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-keyboard text-warning fs-4 me-3"></i>
                                <div>
                                    <strong>Skróty klawiszowe</strong><br>
                                    <small class="text-muted">Szybka nawigacja</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frame Layout Tab -->
        <div class="tab-pane fade" id="frame-layout" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-window-stack me-2"></i>Układ z ramkami - jak to działa</h4>
                        </div>
                        <div class="card-body">
                            <p>Układ z ramkami wykorzystuje technologię iframe do oddzielenia nawigacji od zawartości. Dzięki temu menu pozostaje załadowane w pamięci, a zmienia się tylko główna zawartość strony.</p>
                            
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-layout-sidebar text-primary fs-1 mb-3"></i>
                                        <h5>Układ boczny</h5>
                                        <p class="text-muted">Menu po lewej stronie, maksymalna przestrzeń na zawartość</p>
                                        <small class="text-muted">Idealny dla komputerów stacjonarnych</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-layout-text-window text-success fs-1 mb-3"></i>
                                        <h5>Układ górny</h5>
                                        <p class="text-muted">Menu na górze, kompaktowa nawigacja</p>
                                        <small class="text-muted">Przyjazny dla urządzeń mobilnych</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-layout-three-columns text-info fs-1 mb-3"></i>
                                        <h5>Układ podwójny</h5>
                                        <p class="text-muted">Menu górne i boczne, pełne opcje nawigacji</p>
                                        <small class="text-muted">Maksymalna funkcjonalność</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-gear me-2"></i>Jak włączyć układ z ramkami</h5>
                        </div>
                        <div class="card-body">
                            <ol>
                                <li>Przejdź do <strong>Menu Administracyjnego</strong></li>
                                <li>Kliknij <strong>"Układ z ramkami (szybkie ładowanie)"</strong></li>
                                <li>System załaduje się z nowym układem</li>
                                <li>Możesz dostosować ustawienia w <strong>"Edytor układu"</strong></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-arrow-left-right me-2"></i>Przełączanie między układami</h5>
                        </div>
                        <div class="card-body">
                            <p>Możesz łatwo przełączać się między układami:</p>
                            <ul>
                                <li><strong>Tradycyjny układ</strong> - pełne przeładowanie strony</li>
                                <li><strong>Układ z ramkami</strong> - szybkie ładowanie</li>
                                <li><strong>Dostosowanie</strong> - kolory, czcionki, elementy</li>
                            </ul>
                            <a href="layout_manager.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-palette me-2"></i>Edytor układu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tab -->
        <div class="tab-pane fade" id="navigation" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-compass me-2"></i>Nawigacja w systemie</h4>
                        </div>
                        <div class="card-body">
                            <h5>Podstawowa nawigacja</h5>
                            <p>W układzie z ramkami nawigacja jest znacznie szybsza i bardziej responsywna:</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="bi bi-mouse text-primary me-2"></i>Nawigacja myszką</h6>
                                    <ul>
                                        <li>Kliknij element menu w bocznym panelu</li>
                                        <li>Zawartość zmieni się natychmiast</li>
                                        <li>Menu pozostanie załadowane</li>
                                        <li>Brak migotania strony</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="bi bi-search text-success me-2"></i>Wyszukiwanie</h6>
                                    <ul>
                                        <li>Użyj pola wyszukiwania w górnym pasku</li>
                                        <li>Wpisz frazę i naciśnij Enter</li>
                                        <li>Wyniki pojawią się w głównej zawartości</li>
                                        <li>Szybkie filtrowanie danych</li>
                                    </ul>
                                </div>
                            </div>

                            <h5 class="mt-4">Menu główne</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th>Opis</th>
                                            <th>Skrót</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><i class="bi bi-house me-2"></i>Panel główny</td>
                                            <td>Strona główna systemu</td>
                                            <td><kbd>Ctrl + 1</kbd></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-people me-2"></i>Klienci</td>
                                            <td>Zarządzanie klientami</td>
                                            <td><kbd>Ctrl + 2</kbd></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-pc-display me-2"></i>Urządzenia</td>
                                            <td>Zarządzanie urządzeniami</td>
                                            <td><kbd>Ctrl + 3</kbd></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-hdd-network me-2"></i>Sieci</td>
                                            <td>Konfiguracja sieci</td>
                                            <td><kbd>Ctrl + 4</kbd></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-gear me-2"></i>Usługi</td>
                                            <td>Zarządzanie usługami</td>
                                            <td><kbd>Ctrl + 5</kbd></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-arrow-clockwise me-2"></i>Odświeżanie</h5>
                        </div>
                        <div class="card-body">
                            <h6>Ręczne odświeżanie</h6>
                            <ul>
                                <li><kbd>Ctrl + R</kbd> - odśwież zawartość</li>
                                <li>Przycisk "Odśwież" w interfejsie</li>
                                <li>F5 - pełne odświeżenie strony</li>
                            </ul>
                            
                            <h6 class="mt-3">Automatyczne odświeżanie</h6>
                            <ul>
                                <li>System odświeża co 5 minut</li>
                                <li>Aktualne dane bez interwencji</li>
                                <li>Można wyłączyć w ustawieniach</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcuts Tab -->
        <div class="tab-pane fade" id="shortcuts" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-keyboard me-2"></i>Skróty klawiszowe</h4>
                        </div>
                        <div class="card-body">
                            <p>Skróty klawiszowe znacznie przyspieszają pracę z systemem. Wszystkie skróty działają w układzie z ramkami.</p>
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-compass me-2"></i>Nawigacja</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Skrót</th>
                                                    <th>Akcja</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><kbd>Ctrl + 1</kbd></td>
                                                    <td>Panel główny</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 2</kbd></td>
                                                    <td>Klienci</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 3</kbd></td>
                                                    <td>Urządzenia</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 4</kbd></td>
                                                    <td>Sieci</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 5</kbd></td>
                                                    <td>Usługi</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 6</kbd></td>
                                                    <td>Płatności</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 7</kbd></td>
                                                    <td>Faktury</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 8</kbd></td>
                                                    <td>Użytkownicy</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + 9</kbd></td>
                                                    <td>Administracja</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-tools me-2"></i>Funkcje</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Skrót</th>
                                                    <th>Akcja</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><kbd>Ctrl + R</kbd></td>
                                                    <td>Odśwież zawartość</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + F</kbd></td>
                                                    <td>Fokus na wyszukiwanie</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + K</kbd></td>
                                                    <td>Wyszukiwanie globalne</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + U</kbd></td>
                                                    <td>Profil użytkownika</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>F5</kbd></td>
                                                    <td>Pełne odświeżenie strony</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + S</kbd></td>
                                                    <td>Zapisz (w formularzach)</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Ctrl + Z</kbd></td>
                                                    <td>Cofnij (w formularzach)</td>
                                                </tr>
                                                <tr>
                                                    <td><kbd>Esc</kbd></td>
                                                    <td>Zamknij modal/okno</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <h6><i class="bi bi-lightbulb me-2"></i>Wskazówka</h6>
                                <p class="mb-0">Skróty klawiszowe działają tylko w układzie z ramkami. W tradycyjnym układzie niektóre skróty mogą nie działać poprawnie.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Tab -->
        <div class="tab-pane fade" id="troubleshooting" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-tools me-2"></i>Rozwiązywanie problemów</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Ramki się nie ładują</h5>
                                    <div class="alert alert-warning">
                                        <strong>Objawy:</strong>
                                        <ul class="mb-2">
                                            <li>Puste ramki</li>
                                            <li>Kręcące się kółko ładowania</li>
                                            <li>Błąd "Nie można załadować ramki"</li>
                                        </ul>
                                        <strong>Rozwiązania:</strong>
                                        <ol>
                                            <li>Sprawdź połączenie internetowe</li>
                                            <li>Odśwież stronę (F5)</li>
                                            <li>Sprawdź czy JavaScript jest włączony</li>
                                            <li>Wyczyść pamięć podręczną przeglądarki</li>
                                            <li>Spróbuj innej przeglądarki</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-arrow-right text-danger me-2"></i>Nawigacja nie działa</h5>
                                    <div class="alert alert-danger">
                                        <strong>Objawy:</strong>
                                        <ul class="mb-2">
                                            <li>Kliknięcie w menu nie zmienia zawartości</li>
                                            <li>Zawartość pozostaje taka sama</li>
                                            <li>Błąd w konsoli przeglądarki</li>
                                        </ul>
                                        <strong>Rozwiązania:</strong>
                                        <ol>
                                            <li>Sprawdź konsolę przeglądarki (F12)</li>
                                            <li>Odśwież stronę (F5)</li>
                                            <li>Sprawdź czy wszystkie pliki są dostępne</li>
                                            <li>Wyłącz blokadę reklam</li>
                                            <li>Sprawdź ustawienia bezpieczeństwa</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-x-circle text-danger me-2"></i>Zawartość się nie wyświetla</h5>
                                    <div class="alert alert-info">
                                        <strong>Objawy:</strong>
                                        <ul class="mb-2">
                                            <li>Pusta główna ramka</li>
                                            <li>Komunikat o błędzie</li>
                                            <li>Biały ekran</li>
                                        </ul>
                                        <strong>Rozwiązania:</strong>
                                        <ol>
                                            <li>Sprawdź czy strona docelowa istnieje</li>
                                            <li>Sprawdź połączenie z bazą danych</li>
                                            <li>Sprawdź logi serwera</li>
                                            <li>Spróbuj przejść do innej strony</li>
                                            <li>Skontaktuj się z administratorem</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <h5><i class="bi bi-speedometer text-warning me-2"></i>Wolne ładowanie</h5>
                                    <div class="alert alert-warning">
                                        <strong>Objawy:</strong>
                                        <ul class="mb-2">
                                            <li>Długie czasy ładowania</li>
                                            <li>Zawartość ładuje się powoli</li>
                                            <li>System wydaje się wolniejszy</li>
                                        </ul>
                                        <strong>Rozwiązania:</strong>
                                        <ol>
                                            <li>Sprawdź prędkość internetu</li>
                                            <li>Zamknij inne karty w przeglądarce</li>
                                            <li>Wyczyść pamięć podręczną</li>
                                            <li>Sprawdź obciążenie serwera</li>
                                            <li>Użyj trybu kompatybilności</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Tab -->
        <div class="tab-pane fade" id="faq" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-question-circle me-2"></i>Często zadawane pytania</h4>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            Czy układ z ramkami działa na wszystkich przeglądarkach?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Układ działa na nowoczesnych przeglądarkach:
                                            <ul>
                                                <li>Chrome 60+</li>
                                                <li>Firefox 55+</li>
                                                <li>Safari 12+</li>
                                                <li>Edge 79+</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                            Czy mogę wrócić do tradycyjnego układu?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Tak, zawsze możesz wrócić do tradycyjnego układu:
                                            <ol>
                                                <li>Przejdź do Menu Administracyjnego</li>
                                                <li>Kliknij "Edytor układu"</li>
                                                <li>Wybierz tradycyjny układ</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                            Dlaczego niektóre strony ładują się wolniej?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Różne strony mogą mieć różną złożoność:
                                            <ul>
                                                <li>Strony z dużą ilością danych ładują się dłużej</li>
                                                <li>Strony z obrazami potrzebują więcej czasu</li>
                                                <li>Strony z złożonymi formularzami wymagają więcej zasobów</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                            Czy mogę dostosować układ do swoich potrzeb?
                                        </button>
                                    </h2>
                                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Tak, możesz dostosować:
                                            <ul>
                                                <li>Pozycję menu (górne/boczne/podwójne)</li>
                                                <li>Kolory i motywy</li>
                                                <li>Rozmiar czcionek</li>
                                                <li>Elementy interfejsu</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                            Co zrobić jeśli system nie działa?
                                        </button>
                                    </h2>
                                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Wykonaj następujące kroki:
                                            <ol>
                                                <li>Odśwież stronę (F5)</li>
                                                <li>Sprawdź konsolę przeglądarki (F12)</li>
                                                <li>Wyczyść pamięć podręczną</li>
                                                <li>Spróbuj innej przeglądarki</li>
                                                <li>Skontaktuj się z administratorem</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                            Czy układ z ramkami jest bezpieczny?
                                        </button>
                                    </h2>
                                    <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Tak, system jest bezpieczny:
                                            <ul>
                                                <li>Wszystkie ramki używają tej samej domeny</li>
                                                <li>Brak zewnętrznych ramek</li>
                                                <li>Ochrona przed XSS</li>
                                                <li>Walidacja danych wejściowych</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to active tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = document.querySelector('.nav-link.active');
    if (activeTab) {
        activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});

// Add smooth scrolling to all internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../partials/layout.php';
?> 