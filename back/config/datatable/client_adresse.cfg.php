<?php
/**
 * Configuration de l'affichage des adresses clients
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

$config = array(
    'table' => array(
        'title' => 'Liste des adresses',
        'title_item' => 'adresse',
        'suffix_genre' => 'e',
        'fixedheader' => false,
        'name' => 'client_adresse',
    ),
    'file' => array(
        'upload_path' => '',
        'upload_temp' => 'temp',
        'upload_vignette' => 'mini',
        'upload_apercu' => 'apercu',
    ),
    'extra' => array(
        'copy' => false,
        'print' => false,
        'pdf' => false,
        'csv' => false,
        'hide_columns' => false,
        'highlightedSearch' => false,
        'creable' => false,
        'editable' => true,
        'deletable' => false,
    ),
    'style' => array(
        'form' => 'bootstrap',
    ),
    'columns' => array(
        array(
            'name' => 'id',
            'index' => true,
            'show' => false,
            'filter_field' => 'text',
            'title' => 'Id',
        ),
        array(
            'name' => 'civilite',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Civilité',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'nom',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Nom',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'prenom',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Prénom',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'tel1',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Téléphone',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'adresse1',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Adresse',
        ),
        array(
            'name' => 'adresse2',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Adresse',
        ),
        array(
            'name' => 'ville',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Ville',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'cp',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Code Postal',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'client_id',
            'show' => false,
            'filter_field' => 'text',
            'title' => 'Id_client',
        )
    ),
);

