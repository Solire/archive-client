<?php
/**
 * Template simple de script d'installation
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();
\Slrfw\FrontController::setApp('projet');

$path = \Slrfw\FrontController::search('config/sqlClient.ini', false);
if (empty($path)) {
    echo 'Veuillez paramétrer le fichier sqlClient.ini et le mettre dans projet/config/';
    die;
}
$confSql = new \Slrfw\Config($path);
unset($path);

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

