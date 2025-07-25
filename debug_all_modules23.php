     <?php
     // debug_all_modules.php

     $modules_dir = __DIR__ . '/modules/';
     $log_file = __DIR__ . '/modules_debug.log';

     $module_files = glob($modules_dir . '*.php');
     $results = [];

     foreach ($module_files as $file) {
         $module_name = basename($file);
         ob_start();
         try {
             include_once $file;
             $output = ob_get_clean();
             $results[] = [
                 'module' => $module_name,
                 'status' => 'OK',
                 'output' => $output
             ];
         } catch (Throwable $e) {
             $output = ob_get_clean();
             $results[] = [
                 'module' => $module_name,
                 'status' => 'ERROR',
                 'error' => $e->getMessage(),
                 'output' => $output
             ];
         }
     }

     // Write results to log file
     file_put_contents($log_file, print_r($results, true));

     echo "Debugging complete. See $log_file for details.\n";
     ?>
