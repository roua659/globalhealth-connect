<?php
declare(strict_types=1);

/**
 * URL absolue (depuis la racine du site) vers index.php pour les appels API.
 * Fonctionne que la page soit chargée via index.php ou en ouvrant un fichier sous views/ directement.
 */
function gh_users_api_base(): string
{
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');
    if ($docRoot !== '' && $projectRoot !== '' && str_starts_with($projectRoot, $docRoot)) {
        $base = substr($projectRoot, strlen($docRoot));
        return rtrim($base, '/') . '/index.php?url=api/users';
    }

    return '/index.php?url=api/users';
}
