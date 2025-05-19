<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

global $template;

// Valeurs par défaut si aucune configuration n'existe
$defaultConfig = [
    'ldap_server'            => '',
    'ldap_port'              => '',
    'ldap_base_dn'           => '',
    'ldap_tls_require_cert'  => 'never',
    'ldap_tls_protocol_min'  => 'TLS1_0',
    'ldap_protocol_version'  => '3',
    'ldap_referrals'         => '0',
    'ldap_attributes'        => '',
    'ldap_group_to_add'      => '', 
];

// Récupérer la liste des groupes depuis la table de Piwigo
$groups = array();
$query = 'SELECT id, name FROM '.GROUPS_TABLE.' ORDER BY name ASC';
$result = pwg_query($query);
while ($row = pwg_db_fetch_assoc($result)) {
    $groups[] = $row;
}

// Si le formulaire est posté
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ldapConfig = [
        'ldap_server'           => $_POST['ldap_server'] ?? '',
        'ldap_port'             => $_POST['ldap_port'] ?? '',
        'ldap_base_dn'          => $_POST['ldap_base_dn'] ?? '',
        'ldap_tls_require_cert' => $_POST['ldap_tls_require_cert'] ?? 'never',
        'ldap_tls_protocol_min' => $_POST['ldap_tls_protocol_min'] ?? 'TLS1_0',
        'ldap_protocol_version' => $_POST['ldap_protocol_version'] ?? '3',
        'ldap_referrals'        => $_POST['ldap_referrals'] ?? '0',
        'ldap_attributes'       => $_POST['ldap_attributes'] ?? '',
        // On récupère le groupe sélectionné
        'ldap_group_to_add'     => $_POST['ldap_group_to_add'] ?? '',
    ];

    // Mise à jour en base (table piwigo_config)
    conf_update_param('ldap_login_config', serialize($ldapConfig));

    // Pour la suite du script
    $currentConfig = $ldapConfig;
} else {
    // On récupère la config existante
    $currentConfig = unserialize(conf_get_param('ldap_login_config', serialize($defaultConfig)));
}

// Envoi des valeurs au template
$template->assign([
    'LDAP_SERVER'            => htmlspecialchars($currentConfig['ldap_server'], ENT_QUOTES),
    'LDAP_PORT'              => htmlspecialchars($currentConfig['ldap_port'], ENT_QUOTES),
    'LDAP_BASE_DN'           => htmlspecialchars($currentConfig['ldap_base_dn'], ENT_QUOTES),
    'LDAP_TLS_REQUIRE_CERT'  => htmlspecialchars($currentConfig['ldap_tls_require_cert'], ENT_QUOTES),
    'LDAP_TLS_PROTOCOL_MIN'  => htmlspecialchars($currentConfig['ldap_tls_protocol_min'], ENT_QUOTES),
    'LDAP_PROTOCOL_VERSION'  => htmlspecialchars($currentConfig['ldap_protocol_version'], ENT_QUOTES),
    'LDAP_REFERRALS'         => htmlspecialchars($currentConfig['ldap_referrals'], ENT_QUOTES),
    'LDAP_ATTRIBUTES'        => htmlspecialchars($currentConfig['ldap_attributes'], ENT_QUOTES),
    'LDAP_GROUP_TO_ADD'      => htmlspecialchars($currentConfig['ldap_group_to_add'], ENT_QUOTES),
    'groups'                 => $groups,
]);

// Définir le template d'admin
$template->set_filename('plugin_ldap_login_config', LDAP_PATH.'template/admin_config.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_ldap_login_config');
