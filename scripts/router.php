<?php
// Solo para el servidor de desarrollo PHP. Apache usa los .htaccess del proyecto.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if (
    preg_match(
        '~(?:^|/)(?:data|scripts|docs|\.git|\.vscode|\.superdesign)(?:/|$)|(?:^|/)(?:app|auth|config\.[^/]+)\.php$|\.(?:md|sql|log|env)$~i',
        $path,
    )
) {
    http_response_code(404);
    exit('No encontrado');
}
return false;
