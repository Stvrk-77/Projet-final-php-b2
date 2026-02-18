# Projet E-Commerce PHP (LeBonDuCoin) - Guide Complet

## 📋 Description

Site e-commerce développé en PHP natif avec MySQL pour le projet final du module PHP.

### Fonctionnalités principales

- ✅ Système d'authentification (inscription/connexion)
- ✅ Gestion des articles (CRUD complet)
- ✅ Panier d'achat
- ✅ Système de commande avec factures
- ✅ Gestion du stock
- ✅ Tableau de bord administrateur
- ✅ Gestion des utilisateurs
- ✅ Système de solde et rechargement

## 🛠 Prérequis

- XAMPP (Windows) / MAMP (Mac) / LAMP (Linux) avec PHP 8.0+
- Navigateur web moderne
- Git (pour cloner le projet)

## 📦 Installation

### Étape 1 : Installation de XAMPP/MAMP

**Windows - XAMPP :**
1. Télécharger XAMPP 
2. Installer avec PHP 8.0 ou supérieur
3. Lancer XAMPP Control Panel
4. Démarrer Apache et MySQL

**Mac - MAMP :**
1. Télécharger MAMP
2. Installer et lancer MAMP
3. Vérifier que PHP 8.0+ est sélectionné dans Préférences > PHP
4. Cliquer sur "Start Servers"

**Linux - LAMP :**
```bash
# Ubuntu/Debian
sudo apt-get install lamp-server^

# Démarrer les services
sudo systemctl enable --now httpd
sudo systemctl enable --now mysql
```

### Étape 2 : Cloner le projet

1. **Localiser le dossier htdocs :**
   - Windows (XAMPP) : `C:\xampp\htdocs\`
   - Mac (MAMP) : `/Applications/MAMP/htdocs/`
   - Linux (LAMP) : `/var/www/html/`

2. **Cloner le projet dans htdocs :**
```bash
cd /chemin/vers/htdocs
git clone https://github.com/Stvrk-77/Projet-final-php-b2.git
mv Projet-final-php-b2 php_exam
```

### Étape 3 : Configuration de la base de données

1. **Accéder à phpMyAdmin :**
   - Ouvrir un navigateur
   - Aller sur `http://localhost/phpmyadmin`
   - MAMP : `http://localhost:8888/phpmyadmin`

2. **Créer la base de données :**
   - Cliquer sur "Nouvelle base de données"
   - Nom : `php_exam_db`
   - Collation : `utf8mb4_general_ci`
   - Cliquer sur "Créer"

3. **Importer la structure :**
   - Sélectionner la base `php_exam_db`
   - Cliquer sur l'onglet "Importer"
   - Choisir le fichier `php_exam_db.sql`
   - Cliquer sur "Exécuter"

### Étape 4 : Configuration du projet

**Vérifier le fichier config.php :**

Ouvrir `config.php` et vérifier les paramètres de connexion :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'php_exam_db');
```

### Étape 5 : Structure des fichiers

Organiser les fichiers comme suit :

```
php_exam/
├── admin/
│   ├── index.php
│   ├── articles.php
│   └── users.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
│       └── (vos images produits)
├── config.php
├── session.php
├── header.php
├── footer.php
├── index.php
├── login.php
├── register.php
├── logout.php
├── sell.php
├── detail.php
├── cart.php
├── cart_validate.php
├── edit.php
├── account.php
├── php_exam_db.sql
└── README.md
```

## 🚀 Lancement du projet

1. **Démarrer les serveurs :**
   - XAMPP : Apache et MySQL dans le Control Panel
   - MAMP : Cliquer sur "Start Servers"
   - LAMP : Vérifier que httpd et mysql sont actifs

2. **Accéder au site :**
   - XAMPP/LAMP : `http://localhost/php_exam`
   - MAMP : `http://localhost:8888/php_exam`

## 👤 Compte administrateur par défaut

- **Email :** admin@ecommerce.com
- **Mot de passe :** admin123
- **Solde :** 10 000 €

## 🔑 Fonctionnalités et routes

### Pages publiques (accessibles sans connexion)
- `/` ou `/index.php` - Page d'accueil avec tous les articles
- `/detail.php?id=X` - Détail d'un article
- `/login.php` - Connexion
- `/register.php` - Inscription

### Pages utilisateur (connexion requise)
- `/sell.php` - Créer un article
- `/cart.php` - Panier
- `/cart_validate.php` - Validation de commande
- `/edit.php?id=X` - Modifier un article (auteur ou admin uniquement)
- `/account.php` - Mon compte
- `/account.php?user_id=X` - Profil d'un utilisateur

### Pages administrateur (rôle admin requis)
- `/admin/index.php` - Tableau de bord
- `/admin/articles.php` - Gestion des articles
- `/admin/users.php` - Gestion des utilisateurs

## 📝 Utilisation

### Pour un utilisateur :

1. **S'inscrire** : `/register.php`
   - Solde de départ : 100 €
   
2. **Parcourir les articles** : `/index.php`

3. **Voir un article** : Cliquer sur "Voir les détails"

4. **Ajouter au panier** : Sur la page de détail d'un article

5. **Passer commande** :
   - Aller dans le panier
   - Modifier les quantités si besoin
   - Cliquer sur "Passer la commande"
   - Remplir les informations de facturation
   - Valider

6. **Vendre un article** : `/sell.php`

7. **Gérer son compte** : `/account.php`
   - Modifier email/mot de passe
   - Recharger le solde
   - Voir ses articles
   - Voir ses factures

### Pour un administrateur :

1. **Se connecter** avec le compte admin

2. **Accéder au tableau de bord** : `/admin/index.php`

3. **Gérer les articles** :
   - Voir tous les articles
   - Modifier n'importe quel article
   - Supprimer des articles

4. **Gérer les utilisateurs** :
   - Voir tous les utilisateurs
   - Changer les rôles
   - Supprimer des utilisateurs

## 🗄 Structure de la base de données

### Table `users`
- id (PRIMARY KEY, AUTO_INCREMENT)
- username (UNIQUE)
- email (UNIQUE)
- password (bcrypt)
- balance
- profile_picture
- role (user/admin)
- created_at

### Table `articles`
- id (PRIMARY KEY, AUTO_INCREMENT)
- name
- description
- price
- publication_date
- author_id (FOREIGN KEY → users)
- image_link

### Table `stock`
- id (PRIMARY KEY, AUTO_INCREMENT)
- article_id (FOREIGN KEY → articles)
- quantity

### Table `cart`
- id (PRIMARY KEY, AUTO_INCREMENT)
- user_id (FOREIGN KEY → users)
- article_id (FOREIGN KEY → articles)
- quantity
- added_at

### Table `invoices`
- id (PRIMARY KEY, AUTO_INCREMENT)
- user_id (FOREIGN KEY → users)
- transaction_date
- amount
- billing_address
- billing_city
- billing_zipcode

### Table `invoice_items`
- id (PRIMARY KEY, AUTO_INCREMENT)
- invoice_id (FOREIGN KEY → invoices)
- article_id
- article_name
- quantity
- unit_price

## 🔒 Sécurité implémentée

- ✅ Mots de passe hashés avec bcrypt
- ✅ Protection contre les injections SQL (requêtes préparées)
- ✅ Échappement des données (htmlspecialchars)
- ✅ Vérification des permissions (auteur/admin)
- ✅ Sessions sécurisées
- ✅ Validation des formulaires

## 📚 Technologies utilisées

- **Backend :** PHP 8.0+ (natif, sans framework)
- **Base de données :** MySQL 5.7+
- **Frontend :** HTML5, CSS3
- **Serveur :** Apache

## ✅ Checklist des fonctionnalités

- [x] Inscription/Connexion avec validation
- [x] Username et email uniques
- [x] Connexion automatique après inscription
- [x] Page Home avec tous les articles
- [x] Page Vente pour créer des articles
- [x] Page Détail avec ajout au panier
- [x] Page Panier avec gestion des quantités
- [x] Page Validation de commande
- [x] Page Modification d'article (auteur/admin)
- [x] Page Compte avec gestion profil
- [x] Affichage des factures
- [x] Rechargement du solde
- [x] Tableau de bord administrateur
- [x] Gestion des articles (admin)
- [x] Gestion des utilisateurs (admin)
- [x] Gestion du stock
- [x] Protection des pages selon rôle

## 👥 Équipe

- MAUSSANT Mathéo
- NGUEMA Rodney
