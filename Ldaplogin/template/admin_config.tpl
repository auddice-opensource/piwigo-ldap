{combine_css id='admin_style' path='plugins/Ldaplogin/template/admin_style.css'}

<h2>Configuration LDAP</h2>
<form method="post">
    <div>
        <label for="ldap_server">Serveur LDAP :</label>
        <input type="text" id="ldap_server" name="ldap_server" value="{$LDAP_SERVER}" required>
    </div>
    <div>
        <label for="ldap_port">Port LDAP :</label>
        <input type="number" id="ldap_port" name="ldap_port" value="{$LDAP_PORT}" required>
    </div>
    <div>
        <label for="ldap_base_dn">Base DN :</label>
        <input type="text" id="ldap_base_dn" name="ldap_base_dn" value="{$LDAP_BASE_DN}" required>
    </div>
    <div>
        <label for="ldap_tls_require_cert">TLS Require Cert :</label>
        <select id="ldap_tls_require_cert" name="ldap_tls_require_cert">
            <option value="never" {if $LDAP_TLS_REQUIRE_CERT == 'never'}selected{/if}>never</option>
            <option value="allow" {if $LDAP_TLS_REQUIRE_CERT == 'allow'}selected{/if}>allow</option>
            <option value="try" {if $LDAP_TLS_REQUIRE_CERT == 'try'}selected{/if}>try</option>
            <option value="demand" {if $LDAP_TLS_REQUIRE_CERT == 'demand'}selected{/if}>demand</option>
            <option value="hard" {if $LDAP_TLS_REQUIRE_CERT == 'hard'}selected{/if}>hard</option>
        </select>
    </div>
    <div>
        <label for="ldap_tls_protocol_min">Protocole TLS Minimum :</label>
        <select id="ldap_tls_protocol_min" name="ldap_tls_protocol_min">
            <option value="TLS1_0" {if $LDAP_TLS_PROTOCOL_MIN == 'TLS1_0'}selected{/if}>TLS1.0</option>
            <option value="TLS1_1" {if $LDAP_TLS_PROTOCOL_MIN == 'TLS1_1'}selected{/if}>TLS1.1</option>
            <option value="TLS1_2" {if $LDAP_TLS_PROTOCOL_MIN == 'TLS1_2'}selected{/if}>TLS1.2</option>
            <option value="TLS1_3" {if $LDAP_TLS_PROTOCOL_MIN == 'TLS1_3'}selected{/if}>TLS1.3</option>
        </select>
    </div>
    <div>
        <label for="ldap_protocol_version">Version du protocole LDAP :</label>
        <input type="number" id="ldap_protocol_version" name="ldap_protocol_version" value="{$LDAP_PROTOCOL_VERSION}" min="2" max="3">
    </div>
    <div>
        <label for="ldap_referrals">Referrals (0 ou 1) :</label>
        <input type="number" id="ldap_referrals" name="ldap_referrals" value="{$LDAP_REFERRALS}" min="0" max="1">
    </div>
    <div>
        <label for="ldap_attributes">Attributs supplémentaires (optionnel) :</label>
        <input type="text" id="ldap_attributes" name="ldap_attributes" value="{$LDAP_ATTRIBUTES}">
    </div>

    <!-- Nouveau champ de sélection du groupe Piwigo -->
    <div>
        <label for="ldap_group_to_add">Groupe Piwigo par défaut :</label>
        <select id="ldap_group_to_add" name="ldap_group_to_add">
            <option value="">Aucun</option>
            {foreach from=$groups item=g}
                <option value="{$g.id}" {if $LDAP_GROUP_TO_ADD == $g.id}selected{/if}>{$g.name}</option>
            {/foreach}
        </select>
    </div>

    <div>
        <button type="submit">Enregistrer</button>
    </div>
</form>
