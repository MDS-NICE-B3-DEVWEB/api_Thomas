# Nom de votre API

## Description

Cette API permet de gérer des utilisateurs, des chansons et des beats. Les utilisateurs peuvent avoir différents rôles, tels que 'beatmaker' ou 'artist', et en fonction de leur rôle, ils peuvent avoir des chansons ou des beats associés.

## Installation

1. Clonez le dépôt : `git clone https://github.com/MDS-NICE-B3-DEVWEB/api_Thomas.git`
2. Installez les dépendances : `composer install`
3. Copiez le fichier `.env.example` en `.env` et configurez les variables d'environnement.
4. Lancez les migrations : `php artisan migrate`
5. Lancez le serveur : `php artisan serve`

## Authentification

Cette API utilise l'authentification par token. Pour accéder aux endpoints qui nécessitent une authentification, vous devez inclure un header `Authorization` avec la valeur `Bearer {votre_token}` dans votre requête.

## Endpoints

### Authentification

- `POST /login`: Authentifie un utilisateur et renvoie un token.
  - Données à envoyer : `email` (string, format email), `password` (string).

### Utilisateurs

- `GET /users`: Récupère tous les utilisateurs. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').
- `GET /users/{id}`: Récupère un utilisateur spécifique. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').
- `POST /users`: Crée un nouvel utilisateur. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').
- `PUT /users/{id}`: Met à jour un utilisateur spécifique. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').
- `DELETE /users/{id}`: Supprime un utilisateur spécifique. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').

### Chansons

- `GET /songs`: Récupère toutes les chansons. Nécessite un token en tant que 'artist'.
- `GET /songs/{id}`: Récupère une chanson spécifique. Nécessite un token en tant que 'artist'.
- `POST /songs`: Crée une nouvelle chanson. Nécessite un token en tant que 'artist'.
- `PUT /songs/{id}`: Met à jour une chanson spécifique. Nécessite un token en tant que 'artist'.
- `DELETE /songs/{id}`: Supprime une chanson spécifique. Nécessite un token en tant que 'artist'.

### Beats

- `GET /beats`: Récupère tous les beats. Nécessite un token en tant que 'beatmaker'.
- `GET /beats/{id}`: Récupère un beat spécifique. Nécessite un token en tant que 'beatmaker'.
- `POST /beats`: Crée un nouveau beat. Nécessite un token en tant que 'beatmaker'.
- `PUT /beats/{id}`: Met à jour un beat spécifique. Nécessite un token en tant que 'beatmaker'.
- `DELETE /beats/{id}`: Supprime un beat spécifique. Nécessite un token en tant que 'beatmaker'.

## Tests

Pour lancer les tests, exécutez la commande suivante : `php artisan test`