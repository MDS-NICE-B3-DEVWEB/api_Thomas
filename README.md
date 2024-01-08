# Flow-Forge

## Description

Cette API permet de gérer des utilisateurs, des chansons et des beats. Les utilisateurs peuvent avoir différents rôles, tels que 'beatmaker' ou 'artist', et en fonction de leur rôle, ils peuvent avoir des chansons ou des beats associés.

## Installation avec Docker

1. Assurez-vous d'avoir installé Docker et Docker Compose sur votre machine. Vous pouvez les télécharger à partir de [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop).

2. Clonez ce dépôt sur votre machine locale : `git clone https://github.com/MDS-NICE-B3-DEVWEB/api_Thomas.git`

3. Naviguez jusqu'au répertoire du projet cloné.

4. Exécutez la commande suivante pour construire et démarrer les conteneurs Docker :

    ```bash
    docker-compose up -d
    ```

5. Une fois les conteneurs Docker en cours d'exécution, exécutez les commandes suivantes pour installer les dépendances et configurer l'application :

    ```bash
    docker-compose exec app composer install
    docker-compose exec app cp .env.example .env
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate
    ```
6. Une fois que vous avez configuré votre application, vous devez créer la base de données. Vous pouvez le faire en exécutant la commande suivante :

    ```bash
    docker-compose exec db mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS nom_de_votre_base_de_donnees;"
    ```

    Remplacez "nom_de_votre_base_de_donnees" par le nom que vous souhaitez donner à votre base de données.

7. Ensuite, vous devez modifier le fichier `.env` pour qu'il utilise la base de données que vous venez de créer. Modifiez les valeurs des variables d'environnement suivantes :

    ```env
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=nom_de_votre_base_de_donnees
    DB_USERNAME=root
    DB_PASSWORD=your_mysql_root_password
    ```

    Remplacez "nom_de_votre_base_de_donnees" par le nom de votre base de données et "your_mysql_root_password" par le mot de passe root de votre MySQL.

8. Enfin, exécutez les migrations pour créer les tables dans votre base de données :

    ```bash
    docker-compose exec app php artisan migrate
    ```

    Si vous avez des seeders pour remplir votre base de données avec des données de test, vous pouvez les exécuter avec la commande suivante :

    ```bash
    docker-compose exec app php artisan db:seed
    ```

## En-têtes à utiliser

- `Content-Type: application/json` pour les requêtes qui envoient des données JSON.
- `Content-Type: multipart/form-data` pour les requêtes qui envoient des fichiers.
- `Authorization: Bearer YOUR_TOKEN` pour les routes qui nécessitent une authentification.


## Endpoints

### Authentification

- `POST /login`: Authentifie un utilisateur et renvoie un token.
  - Exemple de données à envoyer : 
    ```json
    {
      "email": "user@example.com",
      "password": "password123"
    }
    ```

### Utilisateurs

- `POST /register`: Crée un nouvel utilisateur.
  - Exemple de données à envoyer : 
    ```json
    {
      "name": "New User",
      "email": "newuser@example.com",
      "password": "password123",
      "role": "artist"
    }
    ```
- `POST /login`: Authentifie un utilisateur et renvoie un token.
  - Exemple de données à envoyer : 
    ```json
    {
      "email": "user@example.com",
      "password": "password123"
    }
    ```
- `PUT /users/{id}`: Met à jour un utilisateur spécifique. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').
  - Exemple de données à envoyer : 
    ```json
    {
      "name": "Updated User",
      "email": "updateduser@example.com",
      "password": "newpassword123"
    }
    ```
- `DELETE /users/{id}`: Supprime un utilisateur spécifique. Nécessite un token (peut être en tant que 'beatmaker' ou 'artist').

### Chansons

- `GET /songs`: Récupère toutes les chansons. Nécessite un token en tant que 'artist'.
- `GET /songs/{id}`: Récupère une chanson spécifique. Nécessite un token en tant que 'artist'.
- `POST /songs`: Crée une nouvelle chanson. Nécessite un token en tant que 'artist'.
  - Données à envoyer (multipart/form-data) : 
    - `title`: (text) "New Song"
    - `audio_file`: (file) Le fichier audio de la chanson.
- `PUT /songs/{id}`: Met à jour une chanson spécifique. Nécessite un token en tant que 'artist'.
  - Exemple de données à envoyer : 
    ```json
    {
      "title": "Updated Song"
    }
    ```
- `DELETE /songs/{id}`: Supprime une chanson spécifique. Nécessite un token en tant que 'artist'.

### Beats

- `GET /beats`: Récupère tous les beats. Nécessite un token en tant que 'beatmaker'.
- `GET /beats/{id}`: Récupère un beat spécifique. Nécessite un token en tant que 'beatmaker'.
- `POST /beats`: Crée un nouveau beat. Nécessite un token en tant que 'beatmaker'.
  - Données à envoyer (multipart/form-data) : 
    - `title`: (text) "New Beat"
    - `audio_file`: (file) Le fichier audio du beat.
- `PUT /beats/{id}`: Met à jour un beat spécifique. Nécessite un token en tant que 'beatmaker'.
  - Exemple de données à envoyer : 
    ```json
    {
      "title": "Updated Beat"
    }
    ```
- `DELETE /beats/{id}`: Supprime un beat spécifique. Nécessite un token en tant que 'beatmaker'.