<?php
/**
 * Chargement de la session client
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Client\Lib;

use Slrfw\Exception\HttpError as Exception;

/**
 * Chargement de la session client
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
trait ClientTrait
{
    /**
     * Charge le compte courent
     * Et lance une 401 si aucun compte n'est disponible
     *
     * @param boolean $displayError Affichage des erreurs
     *
     * @return \Client\Lib\Client|false
     */
    protected function chargeCompte($displayError = true)
    {
        $session = new \Slrfw\Session('client');
        if (!$session->isConnected()) {
            if ($displayError === true) {
                $exc = new Exception('Aucune session de disponible');
                $exc->http(
                    401,
                    \Slrfw\FrontController::$envConfig->get('base', 'url')
                );
                throw $exc;
            }
            return false;
        }

        $className = \Slrfw\FrontController::searchClass('Lib\Client', false);
        $client = new $className($session->id);

        return $client;
    }
}
