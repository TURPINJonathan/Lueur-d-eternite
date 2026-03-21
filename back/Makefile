PHP ?= php
COMPOSER ?= composer
CONSOLE ?= $(PHP) bin/console
DC ?= docker compose

POSTGRES_USER ?= app
POSTGRES_DB ?= app

.PHONY: help \
	install update validate composer-audit \
	up down restart stop ps logs logs-db logs-mail \
	db-up db-down db-reset db-shell \
	cache-clear cache-warmup routes \
	migrate migrations-status migration-diff \
	fixtures load-fixtures \
	cs-check cs-fix lint lint-yaml lint-twig lint-container \
	test qa \
	super-admin

help: ## Affiche toutes les commandes disponibles
	@powershell -NoProfile -Command "[Console]::OutputEncoding = [System.Text.UTF8Encoding]::new($$false); $$OutputEncoding = [Console]::OutputEncoding; $$lines = Get-Content 'Makefile' -Encoding UTF8 | Where-Object { $$_ -match '^[a-zA-Z0-9_.-]+:.*## ' } | Sort-Object; Write-Host ''; Write-Host 'Commandes disponibles:'; Write-Host ''; foreach ($$l in $$lines) { $$parts = $$l -split ':.*## ', 2; ('  {0,-22} {1}' -f $$parts[0], $$parts[1]) }"

install: ## Installe les dépendances PHP (composer install)
	$(COMPOSER) install

update: ## Met à jour les dépendances PHP (composer update)
	$(COMPOSER) update

validate: ## Valide composer.json/composer.lock
	$(COMPOSER) validate

composer-audit: ## Audit de sécurité des dépendances Composer
	$(COMPOSER) audit

up: ## Démarre les services Docker (db + mailer)
	$(DC) up -d

down: ## Arrête et supprime les services Docker
	$(DC) down

restart: down up ## Redémarre les services Docker

stop: ## Stoppe les services Docker sans les supprimer
	$(DC) stop

ps: ## Liste les services Docker
	$(DC) ps

logs: ## Affiche les logs Docker (follow)
	$(DC) logs -f

logs-db: ## Affiche les logs du service database
	$(DC) logs -f database

logs-mail: ## Affiche les logs du service mailer
	$(DC) logs -f mailer

db-up: ## Crée la base si absente
	$(CONSOLE) doctrine:database:create --if-not-exists

db-down: ## Supprime la base si présente
	$(CONSOLE) doctrine:database:drop --if-exists --force

db-reset: db-down db-up migrate ## Reset DB + migrations

db-shell: ## Ouvre un shell psql dans le conteneur database
	$(DC) exec database psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)

cache-clear: ## Vide le cache Symfony
	$(CONSOLE) cache:clear

cache-warmup: ## Préchauffe le cache Symfony
	$(CONSOLE) cache:warmup

routes: ## Liste les routes Symfony
	$(CONSOLE) debug:router

migrate: ## Exécute les migrations Doctrine
	$(CONSOLE) doctrine:migrations:migrate -n

migrations-status: ## Affiche le statut des migrations Doctrine
	$(CONSOLE) doctrine:migrations:status

migration-diff: ## Génère une migration à partir des changements d'entités
	$(CONSOLE) doctrine:migrations:diff

fixtures: ## Charge les fixtures (si doctrine-fixtures-bundle est installé)
	$(CONSOLE) doctrine:fixtures:load -n

load-fixtures: fixtures ## Alias de fixtures

cs-check: ## Vérifie le style PHP (dry-run)
	$(COMPOSER) cs:check

cs-fix: ## Corrige automatiquement le style PHP
	$(COMPOSER) cs:fix

lint: lint-yaml lint-twig lint-container ## Lints Symfony principaux

lint-yaml: ## Vérifie la syntaxe YAML
	$(CONSOLE) lint:yaml config

lint-twig: ## Vérifie la syntaxe Twig
	$(CONSOLE) lint:twig templates

lint-container: ## Vérifie le container Symfony
	$(CONSOLE) lint:container

test: ## Lance la suite de tests PHPUnit
	$(PHP) bin/phpunit

qa: validate lint cs-check test ## Pipeline qualité local (sans modification)

super-admin: ## Crée un super admin (EMAIL=... PASSWORD=... FIRSTNAME=... LASTNAME=...)
	@powershell -NoProfile -Command "if ([string]::IsNullOrWhiteSpace('$(EMAIL)')) { Write-Host 'EMAIL manquant. Exemple: make super-admin EMAIL=admin@site.test PASSWORD=Secret123!'; exit 1 }"
	@powershell -NoProfile -Command "if ([string]::IsNullOrWhiteSpace('$(PASSWORD)')) { Write-Host 'PASSWORD manquant. Exemple: make super-admin EMAIL=admin@site.test PASSWORD=Secret123!'; exit 1 }"
	$(CONSOLE) app:create-super-admin "$(EMAIL)" "$(PASSWORD)" "$(or $(FIRSTNAME),Super)" "$(or $(LASTNAME),Admin)"