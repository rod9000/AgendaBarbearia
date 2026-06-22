<?php
$ftp = ftp_connect('ftpupload.net');
if (!$ftp) { echo "Falha ao conectar ftpupload.net\n"; exit(1); }
echo "Conectado ao FTP\n";
$r = ftp_login($ftp, 'if0_41967135', 'tiUzMXg7kcrfp');
if (!$r) { echo "Login falhou\n"; exit(1); }
echo "Login OK\n";
ftp_pasv($ftp, true);

// List root directories
$rootDirs = ftp_nlist($ftp, '/');
echo "Diretórios raiz:\n";
foreach ($rootDirs as $d) {
    echo "  $d\n";
}

// Search for the domain folder
$found = false;
foreach ($rootDirs as $d) {
    if (stripos($d, 'agendabarbearia') !== false || stripos($d, 'infinityfree') !== false) {
        $found = $d;
        break;
    }
}

if ($found) {
    echo "Pasta encontrada: $found\n";
    if (@ftp_chdir($ftp, $found)) {
        $files = ftp_nlist($ftp, '.');
        foreach ($files as $f) echo "  $f\n";
        if (@ftp_chdir($ftp, 'htdocs')) {
            echo "  htdocs encontrado, listando:\n";
            $hfiles = ftp_nlist($ftp, '.');
            foreach ($hfiles as $hf) echo "    $hf\n";
            
            // Try to read .env
            $tmp = tmpfile();
            if (@ftp_fget($ftp, $tmp, '.env', FTP_ASCII)) {
                rewind($tmp);
                echo "\n=== .env content ===\n";
                echo stream_get_contents($tmp);
                echo "=== end .env ===\n";
            } else {
                echo "Nao foi possivel ler .env\n";
            }
            fclose($tmp);
        }
        ftp_chdir($ftp, '/');
    }
} else {
    echo "Pasta nao encontrada para agendabarbearia\n";
    // Check all directories
    foreach ($rootDirs as $d) {
        if ($d != '.' && $d != '..') {
            echo "Explorando: $d\n";
            if (@ftp_chdir($ftp, $d)) {
                $subs = ftp_nlist($ftp, '.');
                foreach ($subs as $s) echo "  $s\n";
                ftp_chdir($ftp, '/');
            }
        }
    }
}
ftp_close($ftp);
