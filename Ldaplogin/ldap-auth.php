<?php

function ldap_authenticate($username, $password, $config)
{
    $ldapServer = $config['ldap_server'];
    $ldapPort = $config['ldap_port'];
    $baseDn = $config['ldap_base_dn'];
    $cheminLog = __DIR__ . '/debug.log';
    
    // Connexion au serveur LDAP
    $ds = ldap_connect($ldapServer, $ldapPort);
    if (!$ds) {
        return false;
    }

    // Appliquer les options configurées
    // TLS REQUIRE CERT
    $tls_require_cert_map = [
        'never' => LDAP_OPT_X_TLS_NEVER,
        'allow' => LDAP_OPT_X_TLS_ALLOW,
        'try'   => LDAP_OPT_X_TLS_TRY,
        'demand'=> LDAP_OPT_X_TLS_DEMAND,
        'hard'  => LDAP_OPT_X_TLS_HARD,
    ];

    $tls_protocol_map = [
        'TLS1_0' => LDAP_OPT_X_TLS_PROTOCOL_TLS1_0,
        'TLS1_1' => LDAP_OPT_X_TLS_PROTOCOL_TLS1_1,
        'TLS1_2' => LDAP_OPT_X_TLS_PROTOCOL_TLS1_2,
    ];

    // Ajout conditionnel pour TLS1_3 si disponible
    if (defined('LDAP_OPT_X_TLS_PROTOCOL_TLS1_3')) {
    $tls_protocol_map['TLS1_3'] = LDAP_OPT_X_TLS_PROTOCOL_TLS1_3;
    }

    $require_cert = $tls_require_cert_map[$config['ldap_tls_require_cert']] ?? LDAP_OPT_X_TLS_NEVER;
    ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, $require_cert);

    $min_protocol = $tls_protocol_map[$config['ldap_tls_protocol_min']] ?? LDAP_OPT_X_TLS_PROTOCOL_TLS1_0;
    ldap_set_option($ds, LDAP_OPT_X_TLS_PROTOCOL_MIN, $min_protocol);

    // Version du protocole LDAP
    $protocol_version = (int)$config['ldap_protocol_version'] ?: 3;
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $protocol_version);

    // Referrals
    $referrals = (int)$config['ldap_referrals'] ?: 0;
    ldap_set_option($ds, LDAP_OPT_REFERRALS, $referrals);

    // Ici on pourrait éventuellement gérer la vérification du certificat si demandé

    $userDn = "uid={$username},{$baseDn}";
    $result = @ldap_bind($ds, $userDn, $password);

    if (!$result) {
        $error = ldap_error($ds);
        error_log("[LDAP Auth] Échec de l'authentification pour {$username}. Erreur : {$error}\n", 3, $cheminLog);
        ldap_close($ds);
        return false;
    }

    error_log("[LDAP Auth] Authentification réussie pour {$username}.\n", 3, $cheminLog);

    // Recherche des groupes
    $filter = "(uid={$username})";
    $attributes = ['memberof'];
    $search = @ldap_search($ds, $baseDn, $filter, $attributes);

    if ($search) {
        $entries = ldap_get_entries($ds, $search);
        error_log("[LDAP Auth] Résultats LDAP : " . print_r($entries, true) . "\n", 3, $cheminLog);

        $GLOBALS['ldap_groups'] = [];
        if (isset($entries[0]['memberof']) && $entries[0]['memberof']['count'] > 0) {
            for ($i = 0; $i < $entries[0]['memberof']['count']; $i++) {
                $group = $entries[0]['memberof'][$i];
                if (is_string($group)) {
                    $GLOBALS['ldap_groups'][] = $group;
                    error_log("[LDAP Auth] Groupe trouvé : {$group}\n", 3, $cheminLog);
                }
            }

            // Détecter l'agence comme dans le code précédent
            foreach ($GLOBALS['ldap_groups'] as $grp) {
                if (strpos($grp, $groupFilter) !== false) {
                    $agence = substr($grp, 3, 3);
                    error_log("[LDAP Auth] Agence détectée : {$agence}\n", 3, $cheminLog);
                    break;
                }
            }
        } else {
            error_log("[LDAP Auth] Aucun attribut memberof détecté ou aucun groupe.\n", 3, $cheminLog);
        }
    } else {
        $error = ldap_error($ds);
        error_log("[LDAP Auth] Échec de la recherche LDAP pour {$username}. Erreur : {$error}\n", 3, $cheminLog);
        ldap_close($ds);
        return false;
    }

    ldap_close($ds);

    // Retourner agence ou true
    return $agence ?: true;
}
