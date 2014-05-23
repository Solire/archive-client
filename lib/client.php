<?php
/**
 *  Module de gestion des clients.
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Client\Lib;

use \Slrfw\Exception\Lib as Exception,
    \Slrfw\Exception\User as UserException,
    \Slrfw\Registry,
    \Slrfw\FrontController,
    \Slrfw\Config;

/**
 * Fonctionnalités de base d'un client
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Client
{
    /**
     * Chemin vers le fichier de configuration
     */
    const CONFIG_PATH = 'config/client.ini';

    /**
     *
     */
    const FORCE = 18;

    /**
     * Configuration du module client
     * @var Config
     */
    protected $config = null;

    /**
     * Id du client
     * @var int
     */
    protected $id = null;

    /**
     * Données du client
     * @var array
     */
    protected $info = array();

    /**
     * Connection à la BDD
     * @var \Slrfw\MyPDO
     */
    protected $db = null;

    /**
     * Donne un identifiant à l'objet
     *
     * @param int $id Identifiant du client
     *
     * @return void
     * @throws Exception doubleInit
     */
    public function set($id)
    {
        if (!empty($this->id)) {
            throw new Exception($this->config('doubleInit', 'erreur'));
        }

        $this->id = $id;
    }

    /**
     * Enregistre un nouveau client
     *
     * @param array $data Tableau associatif des informations du client
     *
     * @return void
     */
    public function enreg($data)
    {
        $this->id = $this->save($data);
    }

    /**
     * Création d'un nouvel objet client
     *
     * Il faudra ensuite utiliser {@link Client::set()} pour spécifier un id
     * ou {@link Client::enreg()} pour la création d'un nouveau client
     *
     * Il est aussi possible d'appeller le __construct directement avec l'id
     * du compte client
     */
    public function __construct()
    {
        $this->db = Registry::get('db');

        $path = FrontController::search(self::CONFIG_PATH, false);
        $this->config = new Config($path);

        if (func_num_args() == 0) {
            return true;
        }

        $id = func_get_arg(0);
        if (!empty($id)) {
            $this->set($id);
        }
    }

    /**
     * Renvois l'identifiant du client
     *
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * Renvois les informations de configuration des clients
     *
     * @param string $key     Identifiant du paramètre de configuration
     * @param string $section Second Identifiant du paramètre de configuration
     *
     * @return array|string
     */
    public function config($key, $section = null)
    {
        return $this->config->get($key, $section);
    }


    /**
     * Enregistre un nouveau client
     *
     * @param array $data Tableau associatif des informations du client
     *
     * @return int id du client
     */
    protected function save($data)
    {
        $query = 'DESC ' . $this->config('table', 'client') . ';';
        $archi = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);


        $set = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $archi)) {
                if ($key == $this->config->get('table', 'colPassword')) {
                    $value = \Slrfw\Session::prepareMdp($value);
                }
                $set[] = '`' . $key . '` = ' . $this->db->quote($value);
            }
        }

        if (empty($set)) {
            throw new Exception($this->config('insertClientNoData', 'erreur'));
        }

        /* Ajout de la date d'inscription */
        if (in_array('time_inscription', $archi)) {
            $set[] = 'time_inscription = NOW()';
        }

        /* Création du code client */
        if (in_array('code', $archi)) {
            $code = $this->generateCodeClient($data);
            $set[] = 'code = ' . $this->db->quote($code);
            unset($code);
        }

        $insert = 'INSERT INTO ' . $this->config('table', 'client') . ' '
                . 'SET ' . implode(', ', $set);

        try {
            $this->db->exec($insert);
            $this->id = $this->db->lastInsertId();
        } catch (\PDOException $exc) {
            $mask = "#Duplicate entry '([a-z0-9@\.\-]+)' for key '(.+)'#Ui";
            if (preg_match($mask, $exc->getMessage(), $match)) {
                $message = $this->config('insertClientSqlDuplicate', 'erreur');
                $message = sprintf($message, $match[2], $match[1]);
                throw new UserException($message);
            } else {
                throw new Exception($this->config('insertClientSql', 'erreur'));
            }
        }

        $this->addAdress($data);

        return $this->id;
    }

    /**
     * Génère un code client
     *
     * @param array $data Informations du formulaire client
     *
     * @return string
     */
    protected function generateCodeClient($data)
    {
        $codeClient = '';
        for ($i = 0; $i < 3; $i++) {
            $codeClient .= rand(0, 9);
        }
        $codeClient .= strtoupper(substr($data['nom'], 0, 4));
        $codeClient .= strtoupper(substr($data['prenom'], 0, 2));

        for ($i = 0; $i < 5; $i++) {
            $codeClient .= rand(0, 9);
        }

        return $codeClient;
    }

    /**
     * Edite le client, met à jours ses données avec les nouvelles données
     *
     * @param array $data Tableau associatif de données du client
     *
     * @return void
     * @todo revoir l'édition du mot de passe
     */
    public function update(array $data)
    {
        $query = 'DESC ' . $this->config('table', 'client') . ';';
        $archi = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        $set = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $archi)) {
                if ($key == $this->config->get('table', 'colPassword')) {
                    $value = \Slrfw\Session::prepareMdp($value);
                }
                $set[] = '`' . $key . '` = ' . $this->db->quote($value);
            }
        }

        if (empty($set)) {
            throw new Exception($this->config('erreur', 'updateClientNoData'));
        }

        $update = 'UPDATE ' . $this->config('table', 'client') . ' '
                . 'SET ' . implode(', ', $set) . ' '
                . 'WHERE id = ' . $this->id ;
        try {
            $this->db->exec($update);
        } catch (\PDOException $exc) {
            throw new Exception($this->config('erreur', 'updateClientSql'));
        }
    }

    /**
     * Ajouter une adresse au client
     *
     * @param array $data Données de l'adresse
     *
     * @return int identifiant de la nouvelle adresse
     * @throws Esception
     */
    public function addAdress($data)
    {
        $query = 'DESC ' . $this->config('table', 'adresse');
        $archi = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        $data[$this->config('table', 'lienAdresseClient')] = $this->id;
        $set = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $archi)) {
                $set[] = '`' . $key . '` = ' . $this->db->quote($value);
            }
        }

        if (empty($set)) {
            throw new Exception($this->config('erreur', 'insertAdresseNoData'));
            return false;
        }

        $insert = 'INSERT INTO ' . $this->config('table', 'adresse') . ' '
                . 'SET ' . implode(', ', $set);

        $this->db->exec($insert);

        return $this->db->lastInsertId();
    }

    /**
     * Supprime l'adresse
     *
     * On détruit le lien entre l'adresse et l'utilisateur pour ne pas qu'elle
     * continue d'être accessible pour lui. Mais on la laisse en base de donnée
     * pour ne pas faire planter les commandes passées précédements.
     *
     * @param int $idAdresse identifiant de l'adresse
     *
     * @return boolean Vrais si l'adresse était l'adresse principale, faux sinon
     */
    public function rmAdress($idAdresse)
    {
        $opt = array();
        if (func_num_args() > 1) {
            $opt = func_num_args();
            unset($opt[0]);
        }

        $query = 'SELECT principal '
               . 'FROM '. $this->config('adresse', 'table') . ' '
               . 'WHERE id = ' . $idAdresse . ' '
               . ' AND id_client = ' . $this->id;
        $main = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);
        if ($main === false) {
            throw new Exception($this->config('noAdresse', 'erreur'));
        }

        /*
         * Blocage de la suppression des adresses principales
         */
        if ((int)$main == 1 && !in_array(self::FORCE, $opt)) {
            throw new UserException($this->config('supprPrinc', 'erreur'));
        }

        $query = 'UPDATE ' . $this->config('adresse', 'table') . ' '
               . 'SET ' . $this->config('table', 'lienAdresseClient') . ' = 0 '
               . 'WHERE id = ' . $idAdresse . ' '
               . ' AND ' . $this->config('table', 'lienAdresseClient') . ' = ' . $this->id;

        $this->db->exec($query);

        return $main;
    }

    /**
     * Passe une adresse en principale.
     *
     * @param int $idAdresse Identifaint de l'adresse
     *
     * @return void
     */
    public function setPrincipal($idAdresse)
    {
        $query = 'UPDATE ' . $this->config('adresse', 'table')
               . ' SET principal = 0 '
               . 'WHERE ' . $this->config('table', 'lienAdresseClient') . ' = ' . $this->id;
        $this->db->exec($query);

        $query = 'UPDATE ' . $this->config('adresse', 'table')
               . ' SET principal = 1 '
               . 'WHERE id = ' . $idAdresse
               . ' AND ' . $this->config('table', 'lienAdresseClient') . ' = ' . $this->id;
        $this->db->exec($query);
    }

    /**
     * Convertis les champs BIT d'un tableau de données
     * Les champs BIT sont convertis en booleens
     *
     * @param array $data   Tableau qui a des champs BIT
     * @param array $champs Nom des champs BIT
     *
     * @return array Tableau $data avec les champs $champs convertis
     */
    final private function convertionBit(array $data, array $champs)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $champs)) {
                continue;
            }

            $foo = ord($value);

            /*
             * On remplace par des booleans
             */
            if ($foo === 1) {
                $data[$key] = true;
            } else {
                $data[$key] = false;
            }
        }

        return $data;
    }


    /**
     * Enregistre les informations client
     *
     * @param array $info Tableau associatif du contenu de la table client
     *
     * @return void
     */
    public function setInfo($info)
    {
        /*
         * Convertion des champs BIT
         */
        $champs = array('actif');
        $info = $this->convertionBit($info, $champs);

        $this->info = $info;
    }

    /**
     * Renvois toutes les infos d'un client
     *
     * @return array
     */
    public function getInfo()
    {
        $query = 'SELECT * '
               . 'FROM ' . $this->config('table', 'client') . ' c '
               . 'WHERE id = ' . $this->id;
        $info = $this->db->query($query)->fetch(\PDO::FETCH_ASSOC);
        $champs = array('actif');

        $query = 'SELECT * '
               . 'FROM '  . $this->config('table', 'adresse') . ' a '
               . 'WHERE ' . $this->config('table', 'lienAdresseClient') . ' = ' . $this->id;
        $info['adresses'] = $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $this->setInfo($info);

        return $this->info;
    }

    /**
     * Enregistre un message d'historique
     *
     * @param string        $message Message à mettre dans l'historique du client
     * @param DateTime|null $date    Date du message
     *
     * @return void
     */
    public function histo($message, DateTime $date = null)
    {
        $query = 'INSERT INTO client_historique SET '
               . ' message = ' . $this->db->quote($message) . ', '
               . ' ' . $this->config('table', 'lienAdresseClient') . ' = ' . $this->id . ', '
               . ' date_creation = NOW() ';

        if (!empty($date)) {
            $strDate = $this->db->quote($date->format('Y-m-d H:i:s'));
            $query .= ', date_realisation = ' . $strDate . ' ';
        }

        $this->db->exec($query);
    }

    /**
     * Renvois les données du client
     *
     * @param string $name Nom de la variable
     *
     * @return mixed
     * @ignore
     */
    final public function __get($name)
    {
        if (isset($this->info[$name])) {
            return $this->info[$name];
        }

        return null;
    }

    /**
     * Test les données du client
     *
     * @param string $name Nom de la variable
     *
     * @return boolean
     * @ignore
     */
    final public function __isset($name)
    {
        if (isset($this->info[$name])) {
            return true;
        }

        return false;
    }
}
