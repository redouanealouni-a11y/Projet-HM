# Guide de Déploiement Rapide YAMO

## 🚀 Déploiement en 5 minutes

### 1. Configuration

```bash
# Copier la configuration
cp config/config_example.php config/config.php

# Éditer les paramètres de base de données
nano config/config.php
```

**Paramètres à modifier dans config.php :**
```php
$db_config = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'yamo_db',
    'username' => 'votre_utilisateur',
    'password' => 'votre_mot_de_passe'
];
```

### 2. Base de données

```bash
# Créer la base de données
psql -U postgres -c "CREATE DATABASE yamo_db;"

# Importer le schéma
psql -U postgres -d yamo_db -f install/create_database.sql

# Vérifier l'installation
php install/check_requirements.php
```

### 3. Serveur web

#### Apache
```bash
# Modifier le DocumentRoot dans /etc/apache2/sites-available/000-default.conf
DocumentRoot /path/to/YAMO_clean

# Redémarrer Apache
sudo systemctl restart apache2
```

#### Nginx
```bash
# Configuration dans /etc/nginx/sites-available/yamo
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/YAMO_clean;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 4. Permissions

```bash
# Créer le dossier logs
mkdir -p logs
chmod 755 logs

# Permissions des fichiers
chmod -R 644 .
chmod 755 api/
chmod 755 js/
chmod 755 classes/
```

### 5. Test

1. Ouvrir http://yourdomain.com dans un navigateur
2. Vérifier que l'interface se charge
3. Tester l'ajout d'un achat (Nouvel Achat)

## 🔧 Configuration avancée

### HTTPS avec Let's Encrypt

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-apache

# Obtenir le certificat
sudo certbot --apache -d yourdomain.com

# Renouvellement automatique
sudo crontab -e
# Ajouter : 0 12 * * * /usr/bin/certbot renew --quiet
```

### Optimisation des performances

```bash
# Activer la compression gzip dans Apache
sudo a2enmod deflate

# Cache navigateur
sudo a2enmod expires
```

## 📊 Monitoring

### Logs
- Logs Apache/Nginx : `/var/log/apache2/` ou `/var/log/nginx/`
- Logs PHP : Configurer dans `php.ini`
- Logs application : `logs/` (à créer)

### Surveillance
```bash
# Vérifier l'état du serveur
sudo systemctl status apache2
sudo systemctl status postgresql

# Vérifier l'espace disque
df -h

# Vérifier les processus PHP
ps aux | grep php
```

## 🛠️ Dépannage

### Erreur 500 Internal Server Error
```bash
# Vérifier les logs
tail -f /var/log/apache2/error.log
tail -f /var/log/php7.4-fpm.log

# Vérifier les permissions
ls -la logs/
chmod 755 logs/
```

### Erreur de connexion base de données
```bash
# Tester la connexion
psql -h localhost -U username -d database_name

# Vérifier la configuration
php -r "include 'config/config.php';"
```

### Interface ne se charge pas
```bash
# Vérifier les fichiers JavaScript
curl -I http://yourdomain.com/js/main.js
curl -I http://yourdomain.com/js/purchases/purchases-save.js
```

## 📞 Support

En cas de problème :
1. Consultez les logs d'erreur
2. Vérifiez la configuration dans `config/config.php`
3. Assurez-vous que PostgreSQL fonctionne
4. Vérifiez les permissions des fichiers

---

**YAMO est maintenant déployé et prêt à l'utilisation !** 🎉