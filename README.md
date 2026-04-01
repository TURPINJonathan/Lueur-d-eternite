# Lueur-d-eternite

## Déploiement production (non-interactif)

1. Copier l'exemple de configuration:
   - `cp deploy-prod.env.example deploy-prod.env`
2. Compléter les variables dans `deploy-prod.env`.
3. Lancer le déploiement:
   - `bash deploy-prod.sh`
   - ou avec un autre fichier: `bash deploy-prod.sh --env-file /chemin/vers/fichier.env`

Le script `deploy-prod.sh`:
- installe les dépendances back/front,
- génère (ou réutilise) les clés JWT,
- exécute les migrations Doctrine,
- build le front Next.js,
- peut configurer des services `systemd` (front + worker messenger) si `SETUP_SYSTEMD=1`.

Pour une installation interactive locale/dev, conserve `install.sh`.