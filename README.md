# Nom de votre API

## Description

Cette API permet de gérer des utilisateurs, des chansons et des beats. Les utilisateurs peuvent avoir différents rôles, tels que 'beatmaker' ou 'singer', et en fonction de leur rôle, ils peuvent avoir des chansons ou des beats associés.

## Installation

1. Clonez le dépôt : `git clone https://github.com/yourusername/yourrepository.git`
2. Installez les dépendances : `composer install`
3. Copiez le fichier `.env.example` en `.env` et configurez les variables d'environnement.
4. Lancez les migrations : `php artisan migrate`
5. Lancez le serveur : `php artisan serve`

## Endpoints

- `GET /users`: Récupère tous les utilisateurs.
- `GET /users/{id}`: Récupère un utilisateur spécifique.
- `POST /users`: Crée un nouvel utilisateur.
- `PUT /users/{id}`: Met à jour un utilisateur spécifique.
- `DELETE /users/{id}`: Supprime un utilisateur spécifique.

- `GET /songs`: Récupère toutes les chansons.
- `GET /songs/{id}`: Récupère une chanson spécifique.
- `POST /songs`: Crée une nouvelle chanson.
- `PUT /songs/{id}`: Met à jour une chanson spécifique.
- `DELETE /songs/{id}`: Supprime une chanson spécifique.

- `GET /beats`: Récupère tous les beats.
- `GET /beats/{id}`: Récupère un beat spécifique.
- `POST /beats`: Crée un nouveau beat.
- `PUT /beats/{id}`: Met à jour un beat spécifique.
- `DELETE /beats/{id}`: Supprime un beat spécifique.

## Tests

Pour lancer les tests, exécutez la commande suivante : `php artisan test`

## Contribution

Les contributions sont les bienvenues. Veuillez créer une issue ou une pull request pour toute contribution.

## Licence

Cette API est sous licence MIT.