<?php

$sMetadataVersion = '2.0';

$aModule = array(
    'id'          => 'rs-formedit',
    'title'       => '*RS Formedit',
    'description' => 'IDE to develop admin UI',
    'thumbnail'   => '',
    'version'     => '1.0.1',
    'author'      => '',
    'url'         => '',
    'email'       => '',
    'extend'      => array(
        \OxidEsales\Eshop\Application\Controller\Admin\NavigationTree::class => \rs\formedit\Application\Controller\Admin\NavigationTree::class,
    ),
    'controllers' => array(
        'rs_formedit_ide'      => rs\formedit\Application\Controller\Admin\rs_formedit_ide::class,
        'rs_formedit_fullpage' => rs\formedit\Application\Controller\Admin\rs_formedit_fullpage::class,
        'rs_formedit_fullpage_lang' => rs\formedit\Application\Controller\Admin\rs_formedit_fullpage_lang::class,
        'rs_formedit_halfpage' => rs\formedit\Application\Controller\Admin\rs_formedit_halfpage::class,
    ),
    'templates'   => array(
        'rs_formedit_ide.tpl'      => 'rs/formedit/views/admin/tpl/rs_formedit_ide.tpl',
        'rs_formedit_fullpage.tpl' => 'rs/formedit/views/admin/tpl/rs_formedit_fullpage.tpl',
        'rs_formedit_fullpage_lang.tpl' => 'rs/formedit/views/admin/tpl/rs_formedit_fullpage_lang.tpl',
        'rs_formedit_halfpage.tpl' => 'rs/formedit/views/admin/tpl/rs_formedit_halfpage.tpl',
    ),
    'blocks'      => array(),
    'settings'    => array(),
);