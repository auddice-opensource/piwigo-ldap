# LDAP Login Plugin pour Piwigo

## Description
Le plugin LDAP Login permet l'authentification des utilisateurs de Piwigo via un serveur LDAP. Il intègre une interface d'administration simple pour configurer les paramètres LDAP et permet la gestion des utilisateurs et leur intégration automatique dans un groupe Piwigo prédéfini.

## Installation
1. Téléchargez et décompressez le dossier `LdapLogin` dans le répertoire `plugins` de votre installation Piwigo.
2. Activez le plugin dans le menu des plugins de l'administration de Piwigo.

## Configuration
Rendez-vous dans l'administration du plugin (Menu Administrateur → Plugins → LDAP Login) pour configurer les paramètres suivants :

- **Serveur LDAP** : Adresse du serveur LDAP.
- **Port LDAP** : Port utilisé par le serveur LDAP.
- **Base DN** : Base Distinguished Name (DN) à partir de laquelle effectuer les recherches.
- **TLS Require Cert** : Niveau requis de vérification des certificats (never, allow, try, demand, hard).
- **Protocole TLS minimum** : Version minimum du protocole TLS (TLS1.0, TLS1.1, TLS1.2, TLS1.3).
- **Version du protocole LDAP** : Version du protocole LDAP à utiliser (2 ou 3).
- **Referrals** : Indiquez `0` ou `1` pour activer ou non les referrals.
- **Attributs supplémentaires** (optionnel) : Attributs LDAP supplémentaires à récupérer.
- **Groupe Piwigo par défaut** : Groupe Piwigo auquel les nouveaux utilisateurs LDAP seront automatiquement ajoutés.

Sauvegardez les changements en cliquant sur le bouton « Enregistrer ».

## Fonctionnalités
- Authentification sécurisée via LDAP avec support TLS.
- Création automatique des utilisateurs lors de leur première connexion.
- Affectation automatique à un groupe Piwigo prédéfini.
- Configuration simple via l'interface d'administration intégrée.

## Compatibilité
Ce plugin a été testé sur Piwigo 15.3

## Débogage
Un fichier `debug.log` est généré dans le répertoire du plugin pour suivre les erreurs et les actions effectuées lors de l'authentification.

## Auteur
Développé par Sébastien GERARD.

