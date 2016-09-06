<?php

namespace Client\front\controller;

use App\Front\Controller\Main;
use Client\Lib\ClientTrait;
use PDO;
use Projet\lib\Client;
use Slrfw\Config;
use Slrfw\Exception\User;
use Slrfw\Formulaire;
use Slrfw\Formulaire\InstanceTrait;
use Slrfw\FrontController;
use Slrfw\Mail;
use Slrfw\Message;
use Slrfw\Session;

/**
 * Module des comptes utilisateur.
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Compte extends Main
{
    use ClientTrait;
    use InstanceTrait;

    /**
     * Configuration de l'espace client.
     *
     * @var Config
     */
    protected $config;

    /**
     * Chargement de la configuration client.
     *
     * @return void
     */
    public function start()
    {
        parent::start();

        $path = FrontController::search('config/client.ini', false);
        $this->config = new Config($path);

        $this->_view->breadCrumbs[] = [
            'label' => $this->config->get('noms', 'espace'),
            'url' => 'compte/',
        ];
    }

    /**
     * Page d'accueil de l'espace compte.
     *
     * @return void
     * @page Page d'acceuil de l'espace client
     */
    public function startAction()
    {
        $client = $this->chargeCompte();

        // @todo Charger les informations pour le compte client
    }

    /**
     * Déconnexion du client.
     *
     * @return void
     */
    public function deconnexionAction()
    {
        $this->_view->enable(false);

        $client = new Session('client');
        $client->disconnect();

        $url = $this->config->get('url', 'afterdeco');
        $this->simpleRedirect((string) $url, true);
    }

    /**
     * Connexion d'un nouvel utilisateur.
     *
     * @return void
     */
    public function connexionAction()
    {
        $this->_view->enable(false);

        $form = $this->chargeForm('connexion.form.ini');

        list($mail, $password) = $form->run(Formulaire::FORMAT_LIST);

        $client = new Session('client');
        try {
            $client->connect($mail, $password);
        } catch (User $exc) {
            $exc->setErrorInputName($form->getInputNamesList());
            throw $exc;
        }

        $message = new Message($this->_view->_('Connexion Ok'));
        if (isset($form->url)) {
            $message->addRedirect($form->url, 1);
        } else {
            $message->addRedirect('compte/', 1);
        }
        $message->display();
    }

    /**
     * Génère un nouveau mot de passe pour l'utilisateur et lui envois par mail.
     *
     * @return void
     * @mail Mot de passe perdu
     */
    public function mdpPerduAction()
    {
        $this->_view->enable(false);

        $form = $this->chargeForm('mdp.perdu.form.ini');
        $form->run();

        $query = 'SELECT id '
               . 'FROM ' . $this->config->get('table', 'client') . ' c '
               . 'WHERE email = ' . $this->_db->quote($form->email) . ' ';
        $id = $this->_db->query($query)->fetch(PDO::FETCH_COLUMN);

        if (!empty($id)) {
            /* Enregistrement du nouveau mot de passe */
            $data = [];
            $password = Session::makePass();
            $data[$this->config->get('table', 'colPassword')] = $password;
            $className = FrontController::searchClass('Lib\Client');
            $client = new $className($id);
            $client->update($data);

            /* Envois du mot de passe */
            $mail = new Mail('mdp.perdu');
            $mail->to = $form->email;
            $mail->subject = $this->_view->_('Voici votre nouveau mot de passe');
            $mail->password = $password;
            $mail->send();
        }
        $phrase = 'Un email vous a été envoyé.';
        $message = new Message($this->_view->_($phrase));
        $message->display();
    }

    /**
     * Formulaire d'edition du compte.
     *
     * @return void
     * @page Formulaire d'édition du compte client
     */
    public function editionAction()
    {
        $client = $this->chargeCompte();
        $this->_view->client = $client->getInfo();

        $this->_view->breadCrumbs[] = [
            'label' => 'edition',
            'url' => 'compte/edition.html',
        ];
    }

    /**
     * Enregistrement de l'édition d'un compte.
     *
     * @return void
     */
    public function enregistrementEditionAction()
    {
        $this->_view->enable(false);
        $client = $this->chargeCompte();

        $formCompte = $this->chargeForm('client.edition.form.ini');
        $infoClient = $formCompte->run();

        $client->update($infoClient);

        $phrase = 'Modifications correctement enregistrées';
        $message = new Message($this->_view->_($phrase));
        $message->addRedirect('compte/', 2);
        $message->display();
    }

    /**
     * Formulaire d'inscription.
     *
     * @return void
     * @page Inscription utilisateur
     */
    public function inscriptionAction()
    {
        /* Fils d'ariane */
        $this->_view->breadCrumbs[] = [
            'label' => $this->_view->_('inscription'),
            'url' => '',
        ];
    }

    /**
     * Création d'un nouveau compte.
     *
     * @return void
     *
     * @todo mep les envois de mail
     */
    public function enregistrementAction()
    {
        $this->_view->enable(false);

        /* Chargement du formulaire */
        $formCompte = $this->chargeForm('client.form.ini');
        $infoClient = $formCompte->run();

        /* Chargement de la class Client */
        $className = FrontController::searchClass('Lib\Client', false);

        /* @var $client Client */
        $client = new $className();

        /* Enregistrement */
        $client->enreg($infoClient);

        $this->redirect('compte', null);
    }
}
