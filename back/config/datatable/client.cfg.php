<?php
/**
 * Configuration de l'affichage des clients
 *
 * @author  Adrien <aimbert@solire.fr>
 * @license CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

$config = array (
    'table' => array (
        'title' => 'Liste des client',
        'title_item' => 'client',
        'suffix_genre' => '',
        'fixedheader' => true,
        'name' => 'client',
        'detail' => true,
    ),
    'extra' => array (
        'copy' => true,
        'print' => true,
        'pdf' => true,
        'csv' => true,
        'hide_columns' => true,
        'highlightedSearch' => true,
    ),
    'columns' => array (
        array (
            'name' => 'id',
            'show' => false,
            'index' => true,
            'filter_field' => 'text',
            'title' => 'index',
        ),
        array (
            'name' => 'code',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Code',
        ),
        array (
            'name' => 'email',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Email',
        ),
        array(
            'content' => '<div rel="back/dashboard/start.html?'
                . 'name=client_adresse&nomain=1&nojs=1" data-filter='
                . '"filter[]=client_id|[INDEX]" class="ajax-load"></div>',
            'show_detail' => true,
            'title' => 'Options du filtre',
        ),
    ),
);

