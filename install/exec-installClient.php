<?php
/**
 * Template simple de script d'installation
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

include __DIR__ . '/core.php';

/** Mettre script d'installation ici  **/
$query = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'client') . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(18) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time_inscription` datetime NOT NULL,
  `time_edition` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

$query = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'clientAdresse') . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `civilite` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tel1` char(10) COLLATE utf8_unicode_ci NOT NULL,
  `tel2` char(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adresse1` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  `adresse2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cp` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  `ville` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
  `time_inscription` datetime NOT NULL,
  `time_edition` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
';

$db->exec($query);
