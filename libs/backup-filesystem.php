<?php
/*
 * PHP Recursive Backup-Script to ZIP-File
 * (c) 2012: Marvin Menzerath. (http://menzerath.eu)
*/
// Make sure the script can handle large folders/files
ini_set('max_execution_time', 600);
ini_set('memory_limit','2048M');


add_action('wp_ajax_zip_folders', 'zip_folders');



// Start the backup!
//zipData('wp-content/', 'pegaso.zip');
//echo 'Finished.';

// Here the magic happens :)
function zip_folders($folder, $zipTo) {
    echo $folder;
    if (extension_loaded('zip') === true) {
        if (file_exists($folder) === true) {
            $zip = new ZipArchive();

            if ($zip->open($zipTo, ZIPARCHIVE::CREATE) === true) {
                $source = realpath($folder);
                echo $source;
                if (is_dir($source) === true) {
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

                    foreach ($files as $file) {
                        $file = realpath($file);
                        echo $file;
                        if (is_dir($file) === true) {
                            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                        } else if (is_file($file) === true) {
                            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                        }
                    }
                } else if (is_file($source) === true) {
                    $zip->addFromString(basename($source), file_get_contents($source));
                }
            }
            return $zip->close();
        }
    }
    return false;
}
?>