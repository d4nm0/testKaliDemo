<?php
/**
 * Scenario file  
 * 
 * PHP version 5
 * 
 * @category Patch
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @version SVN: $Id$
 * 
 * @link kalifast.com
 */ 

/**
 * Patch 
 * 
 * @category Patch
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @link kalifast.com
 */
class Patch extends BaseApi
{
     /**
     * Application du patch choisi
     * 
     * @return true
     */
    function applyPatch()
    {
        $d = $this->checkParams(
            [
                'patch_name' => 'string'
            ]
        );

        $errors = [];
        //   voir pour retirer les backup auto car avec la 
        //   nouvelle version d'angular les nom sont beaucoup plus long
         
        $errors = $this->backUp();

        // Renommage index.html
        rename("../../compose/public/index.html", "../../compose/public/origin_index.html");

        // Création index patch
        $file = fopen('../../compose/public/index.html', 'w');
        fwrite(
            $file, 
            '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8" />
                <title>Kalifast</title>
                <base href="/" />

                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
                <link rel="icon" type="image/x-icon" href="favicon.ico" />
            </head>
            <h1 style="text-align: center"> Kalifast upgrading to patch '.$d->patch_name.' requested by '.$this->user['username'].' at '.date('d/m/Y h:i:s a', time()).'
            <body>
            </html>
            '
        );
        
        // Suppression du contenu de public
        $dir = '../../compose/public/';
        $files = array_diff(scandir($dir), array('..', '.'));
        foreach ($files as $index => $file) {
            if ($file != '.htaccess' && $file != 'index.html' && $file != 'index.php') {
                if (is_dir('../../compose/public/'.$file)) {
                    $this->deleteDirectory('../../compose/public/'.$file.'/');
                } else {
                    unlink('../../compose/public/'.$file);
                }
            }
        }

        // Application des patchs sql
        $dir = '/var/www/patch/'.$d->patch_name.'/patch/'.$d->patch_name;
        $files = array_diff(scandir($dir), array('..', '.'));

        foreach ($files as $index => $file) {
            $query = file_get_contents($dir.'/'.$file);
            $s = $this->PDO->prepare($query);
            try {
                $s->execute();
            } catch (Exception $e) {
                array_push($errors, $e->getMessage());
            }
            // error_log($s->errorInfo());
        }

        if (count($errors) > 0) {
            $file = fopen('../../patch_error/'.$d->patch_name.'_error.log', 'w');
            foreach ($errors as $i => $e) {
                fwrite($file, $e.'<br>');
                fwrite($file, '---------------'.'<br>');
            }
        }

        // Copie des dossiers et fichiers
        $origin_dir = '../../patch/'.$d->patch_name.'/';
        $destination_dir = '../../compose/';
        $this->copydir($origin_dir, $destination_dir);

        $this->setData(
            [
                'status' => 'Patch done'
            ]
        );
    }

    /**
     * Back up
     * 
     * @return array
     */
    function backUp()
    {
        $file = '../version.txt';
        $patch_name = (int)file_get_contents($file);

        $date = date('y_m_d_H_i_s');
        // Back up de la base
        $filename='../backup/'.$patch_name.'_backup_'.$date.'.sql';

        $CONFIG = getConfig();
        $result=exec(
            'mysqldump '.$CONFIG['DB_NAME'].' --user='.$CONFIG['DB_USER'].' --password='.$CONFIG['DB_PASSWORD'].
            ' --host='.$CONFIG['DB_HOST'].' --single-transaction > ../'.$filename,
            $output
        );

        $errors = [];
        if (empty($output)) {
            // pas d'erreur
        } else {
            array_push($errors, $output);
        }


        // TODO LE BACKUP DE CODE QUAND IL MET PLUS DE 30 SEC RETOURNE UNE ERREUR ET DU COUP IMPOSSIBLE D'APPLIQUER LE PATCH
        // Back up du code
        $p = new PharData('../../backup/'.$patch_name.'_backup_'.$date.'.tar');
        $p->buildFromDirectory('../../compose');
        $p->compress(PHAR::GZ);
        unlink('../../backup/'.$patch_name.'_backup_'.$date.'.tar');

        $this->setData($errors);
        return $errors;
    }

    /**
     * Copie du contenu du dossier
     * 
     * @param string $origine     origine
     * @param string $destination destination
     * 
     * @return int
     */
    function copydir($origine , $destination)
    {
        $dossier=opendir($origine);
        // if (file_exists($destination)) {
        //     return 0;
        // }
        // error_log($destination);
        // error_log($origine);
        try {
            mkdir($destination, fileperms($origine));
        } catch (Exception $e) {
            echo 'Exception reçue : ',  $e->getMessage(), "\n";
        }
        $total = 0;
        while ($fichier = readdir($dossier)) {
            $l = array('.', '..');
            if (!in_array($fichier, $l)) {
                if (is_dir($origine."/".$fichier)) {
                    $total += $this->copydir("$origine/$fichier", "$destination/$fichier");
                } else {
                    if ($fichier != '.env') {
                        copy("$origine/$fichier", "$destination/$fichier");
                        $total++;
                    }
                }
            }
        }
        return $total; 
    }

    /**
     * Suppression d'un dossier
     * 
     * @param string $dir dossier
     * 
     * @return true 
     */
    function deleteDirectory($dir) 
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (array_diff(scandir($dir), array('..', '.')) as $item) {
            if (is_dir($dir.'/'.$item)) {
                $this->deleteDirectory($dir.'/'.$item);
            } else {
                unlink($dir.'/'.$item);
            }
        }

        return rmdir($dir);
    }

    /**
     * Récupération de la liste des backup
     * 
     * @return array
     */
    function getBackupList()
    {
        $folder = '../../backup';
        $backupList = array_diff(scandir($folder), array('..', '.'));

        $logs = [];
        $backup = [];
        foreach ($backupList as $i => $b) {
            if (strpos($b, '.tar.gz')) {
                array_push($backup, $b);

                if (file_exists('../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $b)).'_error.log')) {
                    array_push($logs, 'yes');
                } else {
                    array_push($logs, 'no');
                }
            }
        }

        $this->setData(
            [
                'backup' => $backup,
                'logs' => $logs
            ]
        );
    }

    /**
     * Récupération des logs du backup
     * 
     * @return string
     */
    function getBackupLogs()
    {
        $d = $this->checkParams(
            [
                'backup_name' => 'string'
            ]
        );

        $backup_name = preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name));

        $file = '../../backup/'.$backup_name.'_error.log';
        $content = file_get_contents($file);

        $this->setData($content);
    }

    /**
     * Récupération de la liste des patchs
     * 
     * @return array
     */
    function getPatchList()
    {
        $folder = '../../patch';
        $patches = array_diff(scandir($folder), array('..', '.'));
        $logs = [];

        $patchList = [];
        foreach (array_reverse($patches) as $index => $patch) {
            if (!strpos($patch, '_error.log')) {
                array_push($patchList, preg_replace('/\\.[^.\\s]{3,4}$/', '', $patch));
            }

            if (file_exists('../../patch/'.$patch.'_error.log')) {
                array_push($logs, 'yes');
            } else {
                array_push($logs, 'no');
            }
        }

        $data = file_get_contents('../version.txt');
        $file_data = json_decode($data);

        // error_log(json_encode($patchList));
        $this->setData(
            [
                'patches' => $patchList,
                'version' => $file_data,
                'logs' => $logs
            ]
        );

        return true;
    }

    /**
     * Récupération des logs du patch
     * 
     * @return string
     */
    function getPatchLogs()
    {
        $d = $this->checkParams(
            [
                'patch_name' => 'int'
            ]
        );

        $file = '../../patch_error/'.$d->patch_name.'_error.log';
        $content = file_get_contents($file);

        $this->setData($content);
    }

    /**
     * Restauration du backup
     * 
     * @return true
     */
    function restoreBackup() 
    {
        $d = $this->checkParams(
            [
                'backup_name' => 'string'
            ]
        );

        $errors = [];
        $backup_sql = preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name)).'.sql';

        $CONFIG = getConfig();
        $s = $this->PDO->prepare('DROP DATABASE '.$CONFIG['DB_NAME']);
        try {
            $s->execute();
        } catch (Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $s = $this->PDO->prepare('CREATE DATABASE '.$CONFIG['DB_NAME']);
        try {
            $s->execute();
        } catch (Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $s = $this->PDO->prepare('USE '.$CONFIG['DB_NAME']);
        try {
            $s->execute();
        } catch (Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $file = '../../backup/'.$backup_sql;
        $query = file_get_contents($file);
        $s = $this->PDO->prepare($query);
        try {
            $s->execute();
        } catch (Exception $e) {
            array_push($errors, $e->getMessage());
        }

        $p = new PharData('../../backup/'.$d->backup_name);
        $this->deleteDirectory('../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name)));
        mkdir('../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name)));
        $p->extractTo('../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name)));

        // Suppression de public
        $dir = '../../compose/public/';
        $files = array_diff(scandir($dir), array('..', '.'));
        foreach ($files as $index => $file) {
            if ($file != '.htaccess' && $file != 'index.php') {
                if (is_dir('../../compose/public/'.$file)) {
                    $this->deleteDirectory('../../compose/public/'.$file.'/');
                } else {
                    unlink('../../compose/public/'.$file);
                }
            }
        }

        // Copie des dossiers et fichiers
        $origin_dir = '../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name));
        $destination_dir = '../../compose/';
        $this->copydir($origin_dir, $destination_dir);

        if (count($errors) > 0) {
            $file = fopen('../../backup/'.preg_replace('/\\.[^.\\s]{2,4}$/', '', preg_replace('/\\.[^.\\s]{2,4}$/', '', $d->backup_name)).'_error.log', 'w');
            foreach ($errors as $i => $e) {
                fwrite($file, $e.'<br>');
                fwrite($file, '---------------'.'<br>');
            }
        }

        $this->setData(
            [
                'status' => 'restore done',
                'errors' => $errors
            ]
        );
    }
}