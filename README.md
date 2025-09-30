# 🏥 PharmApp - Système de Gestion de Pharmacie

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Filament-3.x-orange?style=for-the-badge" alt="Filament">
  <img src="https://img.shields.io/badge/Livewire-3.x-purple?style=for-the-badge" alt="Livewire">
</p>

## 📖 Description

**PharmApp** est une application web moderne et complète de gestion de pharmacie développée avec Laravel et Filament. Elle offre une solution intégrée pour la gestion des stocks, des ventes, des fournisseurs et la facturation, avec une interface utilisateur intuitive et des fonctionnalités avancées comme le scan de codes-barres.

## ✨ Fonctionnalités Principales

### 📦 Gestion des Produits
- **Catalogue complet** : DCI, dosage, forme pharmaceutique
- **Codes-barres** : Identification rapide des médicaments
- **Gestion des prix** : Prix d'achat et de vente
- **Suivi des stocks** : Alertes de stock minimum automatiques
- **Traçabilité** : Dates d'expiration et numéros de lot
- **Images** : Support des photos de produits

### 💰 Point de Vente (POS)
- **Scanner de codes-barres** intégré en temps réel
- **Interface de caisse** moderne et responsive
- **Calcul automatique** : TVA (20%), remises, totaux
- **Gestion des paiements** : Montants reçus et calcul de monnaie
- **Validation** : Vérification automatique des stocks

### 📊 Gestion des Ventes
- **Historique complet** des transactions
- **Détails des ventes** : Articles, quantités, prix
- **Génération de factures PDF** avec QR codes
- **Suivi des performances** de vente

### 📈 Gestion des Stocks
- **Mouvements automatiques** : Entrées/sorties de stock
- **Historique détaillé** : Achats, ventes, pertes, ajustements
- **Alertes intelligentes** : Notifications de stock faible
- **Mise à jour en temps réel** lors des ventes

### 🏢 Configuration Pharmacie
- **Informations légales** : SIRET, numéro de licence
- **Coordonnées complètes** : Adresse, téléphone, email
- **Personnalisation** : Logo et pied de page des factures

### 👥 Gestion des Utilisateurs
- **Authentification sécurisée**
- **Système de rôles et permissions** (Spatie)
- **Contrôle d'accès granulaire**

### 🚚 Gestion des Fournisseurs
- **Base de données fournisseurs**
- **Liaison avec les produits**
- **Historique des commandes**

## 🛠️ Technologies Utilisées

- **Backend** : Laravel 12.x (PHP 8.2+)
- **Interface Admin** : Filament 3.x
- **Composants Interactifs** : Livewire 3.x
- **Base de données** : SQLite (configurable)
- **PDF Generation** : DomPDF
- **QR Codes** : SimpleSoftwareIO QrCode
- **Permissions** : Spatie Laravel Permission
- **Frontend** : Vite + TailwindCSS

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- Extension PHP SQLite (ou MySQL/PostgreSQL)
- Extension PHP GD (pour les QR codes)

## 🚀 Installation

### 1. Cloner le repository
```bash
git clone Ultra2000/pharmapp
cd pharmapp
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances Node.js
```bash
npm install
```

### 4. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configuration de la base de données
Modifier le fichier `.env` selon vos besoins :
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 6. Exécuter les migrations
```bash
php artisan migrate --seed
```

### 7. Créer un utilisateur administrateur
```bash
php artisan make:filament-user
```

### 8. Compiler les assets
```bash
npm run build
```

## 🏃‍♂️ Démarrage Rapide

### Développement
```bash
# Démarrer tous les services en parallèle
composer run dev

# Ou individuellement :
php artisan serve
npm run dev
php artisan queue:work
```

### Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## 📚 Utilisation

### Accès à l'application
- **Interface Admin** : `http://localhost:8000/admin`
- **Point de Vente** : `http://localhost:8000/admin/cash-register-page`

### Premier pas
1. Connectez-vous avec votre compte administrateur
2. Configurez les informations de votre pharmacie
3. Ajoutez vos fournisseurs
4. Importez ou créez votre catalogue de produits
5. Commencez à vendre !

## 🔧 Configuration

### Scanner de Codes-barres
Pour utiliser le scanner de codes-barres, assurez-vous que :
- Votre navigateur supporte l'API Camera
- L'application est servie en HTTPS (requis pour l'accès à la caméra)

### Facturation
Les factures sont générées automatiquement en PDF avec :
- Informations de la pharmacie
- Détails de la vente
- QR code pour vérification
- Mentions légales

## 📱 Interface Utilisateur

L'application utilise **Filament** pour offrir :
- Interface moderne et responsive
- Tableaux de données interactifs
- Formulaires intelligents avec validation
- Notifications en temps réel
- Dashboard avec statistiques

## 🔒 Sécurité

- Authentification Laravel Sanctum
- Validation des données côté serveur
- Protection CSRF
- Contrôle d'accès basé sur les rôles
- Logs d'activité des utilisateurs

## 🐛 Débogage

### Logs
```bash
# Visualiser les logs en temps réel
php artisan pail

# Ou consulter les fichiers de logs
tail -f storage/logs/laravel.log
```

### Tests
```bash
php artisan test
```

## 📊 Structure du Projet

```
app/
├── Filament/           # Ressources Filament (admin)
├── Http/Controllers/   # Contrôleurs HTTP
├── Livewire/          # Composants Livewire (POS)
├── Models/            # Modèles Eloquent
├── Services/          # Services métier
└── Policies/          # Politiques d'autorisation

database/
├── migrations/        # Migrations de base de données
└── seeders/          # Données de test

resources/
├── views/            # Templates Blade
└── css/js/           # Assets frontend
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Pushez vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

## 📝 Changelog

### Version 1.0.0
- Gestion complète des produits et stocks
- Point de vente avec scanner de codes-barres
- Génération de factures PDF
- Interface d'administration Filament
- Système de permissions utilisateur

## 🐛 Support & Issues

Si vous rencontrez des problèmes :
1. Vérifiez les [issues existantes](https://github.com/votre-repo/issues)
2. Créez une nouvelle issue avec :
   - Description détaillée du problème
   - Étapes pour reproduire
   - Version de PHP/Laravel
   - Logs d'erreur

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

Développé avec ❤️ par Fréjus BOURAIMA pour moderniser la gestion pharmaceutique.

---

⭐ **N'oubliez pas de donner une étoile si ce projet vous aide !**
