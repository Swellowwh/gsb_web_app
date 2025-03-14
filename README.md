# Projet GSB - Gestion de frais

## Introduction

GSB (Galaxy Swiss Bourdin) est une application de gestion pour laboratoire pharmaceutique développée dans le cadre d'un projet d'étude. Cette application démontre mes compétences en développement web avec des technologies modernes.

L'application permet la gestion des différents acteurs du laboratoire pharmaceutique avec une architecture de rôles multiples :

- **Administrateurs** : Gestion complète de l'application et des utilisateurs
- **Visiteurs médicaux** : Accès aux fonctionnalités de gestion de frais lors des différents déplacements
- **Comptables** : Manipulation et vérification des données financières des visiteurs

Le système d'authentification utilise des tokens JWT pour sécuriser les accès, et chaque rôle dispose de fonctionnalités spécifiques adaptées à ses besoins.

> **Note** : Ce projet a été développé dans un cadre éducatif et n'est pas destiné à être déployé en production. Il sert à démontrer mes compétences en développement web.

## Technologies utilisées

- **Frontend** : Vue.js avec Vite
- **CSS** : Tailwind CSS 3.4.17
- **Backend** : PHP
- **Base de données** : MariaDB
- **Gestion BDD** : PhpMyAdmin
- **State Management** : Pinia
- **Authentification** : Firebase JWT
- **Configuration** : vlucas/phpdotenv

## Installation des dépendances

### Prérequis

- Node.js (v16 ou supérieur)
- PHP 8.0 ou supérieur
- MariaDB
- Composer

### Installation du frontend

1. Cloner le dépôt
```bash
git clone https://github.com/votre-nom/projet-gsb.git
cd projet-gsb/frontend
```

2. Installer les dépendances
```bash
npm install
```

3. Installer Vue.js avec Vite
```bash
npm create vite@latest . -- --template vue
```

4. Installer Tailwind CSS
```bash
npm install -D tailwindcss@3.4.17 postcss autoprefixer
npx tailwindcss init -p
```

5. Configurer Tailwind CSS (tailwind.config.js)
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

6. Installer Pinia pour la gestion des stores
```bash
npm install pinia
```

7. Installer Firebase JWT pour l'authentification
```bash
npm install firebase jwt-decode
```

### Installation du backend

1. Naviguer vers le dossier backend
```bash
cd ../backend
```

2. Installer les dépendances PHP avec Composer
```bash
composer init
composer require vlucas/phpdotenv
composer require firebase/php-jwt
```

3. Configurer le fichier .env
```
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=gsb
JWT_SECRET=votre_secret_jwt
```

### Configuration de la base de données

1. Créer une base de données dans MariaDB via PhpMyAdmin ou en ligne de commande
```sql
CREATE DATABASE gsb;
USE gsb;
```

2. Importer le schéma de base de données fourni
```bash
mysql -u root -p gsb < database/schema.sql
```

## Lancement de l'application

### Frontend
```bash
cd frontend
npm run dev
```

### Backend
```bash
cd backend
php -S localhost:8000
```

L'application sera accessible à l'adresse : http://localhost:5173 pour le frontend, avec le backend sur http://localhost:8000.

## Structure des rôles

- **Administrateurs** : Accès complet à toutes les fonctionnalités et gestion des utilisateurs
- **Visiteurs médicaux** : Création de rapports de visite, gestion des frais,
- **Comptables** : Validation des notes de frais, mise en paiement fictive.

## Fonctionnalités principales

- Authentification par token JWT
- Gestion des profils utilisateurs pour l'administrateur
- Gestion de frais pour les visiteurs médicaux traité par les comptables de l'entreprise pour effectuer des remboursements
- Tableau de bord personnalisé selon le rôle de l'utilisateur
