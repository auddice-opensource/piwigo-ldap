<?php

/*
Plugin Name: LDAP Login
Description: Authentification LDAP simplifiée pour Piwigo.
Version: 1.0.0
Author: Sébastien GERARD
Has Settings: webmaster
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

// Définir l'ID du plugin (le dossier doit s'appeler LdapLogin)
define('LDAP_ID', basename(dirname(__FILE__)));
define('LDAP_PATH', PHPWG_PLUGINS_PATH . LDAP_ID . '/');

// Inclure l'authentification LDAP
$ldapAuthPath = LDAP_PATH . 'ldap-auth.php';
if (file_exists($ldapAuthPath)) {
    include_once($ldapAuthPath);
} else {
    die('Fichier ldap-auth.php introuvable.');
}

// Inclure les fonctions utilisateur
include_once(PHPWG_ROOT_PATH.'include/functions_user.inc.php');

// Hook d'authentification
add_event_handler('try_log_user', 'ldap_login_try_log_user', EVENT_HANDLER_PRIORITY_NEUTRAL);

function ldap_login_try_log_user($password_is_hash, $username, $password, $remember_me)
{
    global $conf, $prefixe_table;
    $cheminLog = __DIR__ . '/debug.log';

    // Récupérer la configuration sérialisée en base
    $ldapConfig = unserialize(conf_get_param('ldap_login_config', serialize([
        'ldap_server'      => '',
        'ldap_port'        => '',
        'ldap_base_dn'     => '',
        'ldap_group_to_add'=> '',
    ])));

    // Vérifier configuration minimale
    if (empty($ldapConfig['ldap_server']) || empty($ldapConfig['ldap_port']) || empty($ldapConfig['ldap_base_dn'])) {
        return false;
    }

    if (empty($prefixe_table)) {
        $prefixe_table = 'piwigo_';
    }

    if (!isset($conf['user_table'])) {
        $conf['user_table'] = $prefixe_table.'users';
    }

    $conf['user_infos_table'] = $prefixe_table.'user_infos';

    // Vérifier identifiants
    if (empty($username) || empty($password)) {
        return false;
    }

    // Authentifier via LDAP
    $ldap_result = ldap_authenticate($username, $password, $ldapConfig);
    if (!$ldap_result) {
            // Ajout du message d'erreur personnalisé
        global $page;
        return false;
    }

    // Inclure fonctions d'admin (pour get_userid/register_user/log_user)
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

    // Chercher si l'utilisateur existe déjà
    $userid = get_userid($username);

    // S'il n'existe pas, on le crée
    if (!$userid) {
        // Création de l'utilisateur
        $new_user_id = register_user($username, $password, '', false);

        // Statut par défaut
        $status = 'normal';

        // Vérifier l'ID de l'utilisateur
        if (isset($GLOBALS['user']['id'])) {
            $userid = $new_user_id;
        } else {
            error_log("[LDAP Login] Impossible de récupérer l'ID de l'utilisateur connecté.\n", 3, $cheminLog);
            return false; // On arrête si l'ID n'est pas récupéré
        }

        // Mettre à jour le statut dans la table user_infos
        single_update(
            $conf['user_infos_table'],
            ['status' => $status],
            ['user_id' => $new_user_id]
        );

        // Récupération du groupe à ajouter depuis la config
        $group_id_to_add = !empty($ldapConfig['ldap_group_to_add']) ? (int)$ldapConfig['ldap_group_to_add'] : 0;

        // Si un groupe est sélectionné (ID > 0), on ajoute le nouvel utilisateur à ce groupe
        if ($group_id_to_add > 0) {
            $check_query = '
                SELECT COUNT(*)
                FROM ' . $prefixe_table . 'user_group
                WHERE user_id = ' . (int)$userid . '
                  AND group_id = ' . (int)$group_id_to_add . '
            ';
            $check_result = pwg_query($check_query);
            list($count) = pwg_db_fetch_row($check_result);

            if ($count == 0) {
                // Ajouter l'utilisateur au groupe
                $insert_query = '
                    INSERT INTO ' . $prefixe_table . 'user_group (user_id, group_id)
                    VALUES (' . (int)$userid . ', ' . (int)$group_id_to_add . ')
                ';
                pwg_query($insert_query);
                error_log("[LDAP Login] Utilisateur ajouté au groupe (ID: {$group_id_to_add}, User ID: {$userid}).\n", 3, $cheminLog);
            } else {
                error_log("[LDAP Login] L'utilisateur est déjà dans le groupe (ID: {$group_id_to_add}, User ID: {$userid}).\n", 3, $cheminLog);
            }
        }
    }

    // Enfin, on connecte l'utilisateur
    log_user($userid, $remember_me);

    return $username;
}
