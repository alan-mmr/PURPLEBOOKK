<?php
$filePath = __DIR__ . '/wilayah_indonesia_pg.sql';
$content = file_get_contents($filePath);
// Fix PostgreSQL escaping: Replace \' with ''
$content = str_replace("\\'", "''", $content);
file_put_contents($filePath, $content);
echo "Berhasil memperbaiki escaping SQL PostgreSQL.\n";
