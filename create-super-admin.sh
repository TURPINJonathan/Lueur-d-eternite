#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ge 4 ]; then
  FIRSTNAME="$1"
  LASTNAME="$2"
  EMAIL="$3"
  PASSWORD="$4"
else
  echo "=== Création d'un SUPER_ADMIN ==="

  read -r -p "Prénom : " FIRSTNAME
  while [ -z "${FIRSTNAME}" ]; do
    echo "Le prénom est obligatoire."
    read -r -p "Prénom : " FIRSTNAME
  done

  read -r -p "Nom : " LASTNAME
  while [ -z "${LASTNAME}" ]; do
    echo "Le nom est obligatoire."
    read -r -p "Nom : " LASTNAME
  done

  read -r -p "Email : " EMAIL
  while [ -z "${EMAIL}" ]; do
    echo "L'email est obligatoire."
    read -r -p "Email : " EMAIL
  done

  read -r -s -p "Mot de passe (au moins 8 caractères) : " PASSWORD
  echo
  while [ -z "${PASSWORD}" ]; do
    echo "Le mot de passe est obligatoire."
    read -r -s -p "Mot de passe : " PASSWORD
    echo
  done
fi

php back/bin/console app:create-super-admin "$EMAIL" "$PASSWORD" "$FIRSTNAME" "$LASTNAME"

echo "Super admin créé pour: $EMAIL"
