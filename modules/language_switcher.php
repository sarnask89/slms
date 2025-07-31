<?php
require_once 'module_loader.php';

require_once __DIR__ . '/../helpers/localization.php';

$pageTitle = __('Language Settings');

// Handle language change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['language'])) {
    $language = $_POST['language'];
    $localization = Localization::getInstance();
    
    if (in_array($language, array_keys($localization->getAvailableLanguages()))) {
        $localization->setLanguage($language);
        $message = __('Language changed successfully');
        $messageType = 'success';
    } else {
        $message = __('Invalid language selected');
        $messageType = 'error';
    }
}

$localization = Localization::getInstance();
$currentLanguage = $localization->getLanguage();
$availableLanguages = $localization->getAvailableLanguages();
$translationStats = $localization->getTranslationStats();

ob_start();
?>

<div class="container">
    <div class="lms-card p-4 mt-4">
        <h2 class="lms-accent mb-4"><?= __('Language Settings') ?></h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Current Language') ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="language" class="form-label"><?= __('Select Language') ?></label>
                                <select class="form-select" id="language" name="language" onchange="this.form.submit()">
                                    <?php foreach ($availableLanguages as $code => $name): ?>
                                        <option value="<?= $code ?>" <?= $code === $currentLanguage ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn lms-btn-accent"><?= __('Change Language') ?></button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Translation Statistics') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 class="text-primary"><?= $translationStats['total'] ?></h3>
                                    <small class="text-muted"><?= __('Total Strings') ?></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h3 class="text-success"><?= $translationStats['translated'] ?></h3>
                                    <small class="text-muted"><?= __('Translated') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $translationStats['percentage'] ?>%"
                                     aria-valuenow="<?= $translationStats['percentage'] ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?= $translationStats['percentage'] ?>%
                                </div>
                            </div>
                            <small class="text-muted"><?= __('Translation Progress') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Language Information') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><?= __('Current Language') ?>: <?= $availableLanguages[$currentLanguage] ?></h6>
                                <p class="text-muted">
                                    <?= __('Language Code') ?>: <code><?= $currentLanguage ?></code>
                                </p>
                                <p class="text-muted">
                                    <?= __('Date Format') ?>: <?= $localization->formatDate(date('Y-m-d')) ?>
                                </p>
                                <p class="text-muted">
                                    <?= __('Time Format') ?>: <?= $localization->formatTime(date('H:i:s')) ?>
                                </p>
                                <p class="text-muted">
                                    <?= __('Number Format') ?>: <?= $localization->formatNumber(1234.56) ?>
                                </p>
                                <p class="text-muted">
                                    <?= __('Currency Format') ?>: <?= $localization->formatCurrency(1234.56) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6><?= __('Available Languages') ?></h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($availableLanguages as $code => $name): ?>
                                        <li>
                                            <span class="badge <?= $code === $currentLanguage ? 'bg-primary' : 'bg-secondary' ?> me-2">
                                                <?= strtoupper($code) ?>
                                            </span>
                                            <?= htmlspecialchars($name) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Translation Examples') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= __('English') ?></th>
                                        <th><?= __('Polish') ?></th>
                                        <th><?= __('Current') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Dashboard</td>
                                        <td>Panel główny</td>
                                        <td><?= __('Dashboard') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Clients</td>
                                        <td>Klienci</td>
                                        <td><?= __('Clients') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Devices</td>
                                        <td>Urządzenia</td>
                                        <td><?= __('Devices') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Settings</td>
                                        <td>Ustawienia</td>
                                        <td><?= __('Settings') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Save</td>
                                        <td>Zapisz</td>
                                        <td><?= __('Save') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Cancel</td>
                                        <td>Anuluj</td>
                                        <td><?= __('Cancel') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Help & Support') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><?= __('How to Change Language') ?></h6>
                                <ol>
                                    <li><?= __('Select your preferred language from the dropdown above') ?></li>
                                    <li><?= __('Click "Change Language" button') ?></li>
                                    <li><?= __('The page will reload with the new language') ?></li>
                                    <li><?= __('Your language preference will be saved for future visits') ?></li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6><?= __('Translation Support') ?></h6>
                                <p><?= __('If you notice any untranslated text or incorrect translations, please contact the support team.') ?></p>
                                <p><?= __('We are continuously working to improve the translation coverage.') ?></p>
                                <a href="mailto:support@slms.local" class="btn btn-outline-primary btn-sm">
                                    <?= __('Contact Support') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeLanguage(language) {
    // AJAX language change
    fetch('language_switcher.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'language=' + encodeURIComponent(language)
    })
    .then(response => response.text())
    .then(data => {
        // Reload page to apply new language
        window.location.reload();
    })
    .catch(error => {
        console.error('Error changing language:', error);
        // Fallback to form submission
        document.querySelector('form').submit();
    });
}

// Auto-submit form when language is changed
document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language');
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once 'content_wrapper.php';
?> 