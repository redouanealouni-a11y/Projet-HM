# YAMO - Gestion de Caisse Régie

## Description

YAMO est une application web de gestion de caisse régie développée en PHP/PostgreSQL. Elle permet de gérer les transactions financières, les achats, les ventes, et les comptes de manière intuitive.

## Fonctionnalités principales

- ✅ Gestion des transactions financières
- ✅ Gestion des achats et ventes
- ✅ Gestion des comptes et tiers
- ✅ Upload et gestion de documents
- ✅ Génération de rapports et statistiques
- ✅ Interface responsive avec Tailwind CSS

## Prérequis

- PHP >= 7.4
- PostgreSQL >= 12
- Apache/Nginx
- Extensions PHP : PDO, pdo_pgsql, json, mbstring

## Installation

1. **Configuration de la base de données**
   - Copiez `config/config_example.php` vers `config/config.php`
   - Modifiez les paramètres de connexion à la base de données

2. **Création de la base de données**
   ```sql
   -- Exécutez le script d'installation
   psql -U username -d database_name -f install/create_database.sql
   ```

3. **Configuration du serveur web**
   - Pointez le DocumentRoot vers le dossier de l'application
   - Assurez-vous que les permissions d'écriture sont configurées pour le dossier `logs/`

## Structure du projet

```
YAMO/
├── api/              # API REST pour les opérations CRUD
├── classes/          # Classes PHP (Database, Transaction, etc.)
├── config/           # Configuration (database.php, config_example.php)
├── includes/         # Helpers et fonctions utilitaires
├── install/          # Scripts d'installation et configuration
├── js/               # JavaScript frontend
│   ├── main.js       # Script principal
│   └── purchases/    # Module de gestion des achats
└── index.php         # Interface utilisateur principale
```

## API Endpoints

### Transactions
- `POST /api/transactions.php` - Créer une transaction
- `GET /api/transactions.php?action=list` - Lister les transactions

### Pièces de trésorerie (Achats/Ventes)
- `POST /api/pieces_tresorerie.php?action=create_achat` - Créer un achat
- `POST /api/pieces_tresorerie.php?action=create_vente` - Créer une vente
- `GET /api/pieces_tresorerie.php?action=list` - Lister les documents

### Tiers (Clients/Fournisseurs)
- `GET /api/tiers.php?type=fournisseur` - Liste des fournisseurs
- `GET /api/tiers.php?type=client` - Liste des clients

### Comptes
- `GET /api/comptes.php` - Liste des comptes bancaires

## Configuration

### Base de données
Modifiez `config/config.php` avec vos paramètres :

```php
<?php
$db_config = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'yamo_db',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

### Paramètres application
Modifiez les paramètres dans `config/config.php` selon vos besoins.

## Développement

### Ajouter une nouvelle fonctionnalité

1. **API** : Créez un nouveau endpoint dans `/api/`
2. **Frontend** : Ajoutez la logique JavaScript dans `/js/`
3. **Interface** : Modifiez `index.php` pour l'interface utilisateur

### Structure des données

Les données sont structurées selon les principes suivants :
- Transactions : Mouvement financier (dépôt, retrait, virement)
- Pièces de trésorerie : Documents commerciaux (factures, devis)
- Tiers : Entités (clients, fournisseurs)
- Comptes : Comptes bancaires

## Sécurité

- ✅ Validation des entrées utilisateur
- ✅ Protection CSRF
- ✅ Requêtes préparées PDO
- ✅ Headers de sécurité CORS configurés

## Support

Pour toute question ou problème, consultez la documentation dans le dossier `/docs/` ou créez une issue.

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

---

**Auteur** : MiniMax Agent  
**Version** : 1.0.0  
**Date** : 2025-11-03