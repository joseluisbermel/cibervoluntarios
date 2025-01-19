# Makefile

# Define el servicio PHP del docker-compose
PHP_SERVICE=docker exec api_motorbike_php

# Inicializa el proyecto
init-project:
	docker-compose up -d --build
	$(PHP_SERVICE) composer install
	cp .env.dev .env
	$(PHP_SERVICE) php bin/console lexik:jwt:generate-keypair

# Actualiza el esquema de la base de datos
update-database-schema:
	$(PHP_SERVICE) php bin/console doctrine:database:create
	$(PHP_SERVICE) php bin/console doctrine:migrations:migrate

# Carga las fixtures en la base de datos
load-fixtures-data:
	$(PHP_SERVICE) php bin/console doctrine:fixtures:load

# Ejecuta los tests del proyecto
tests:
	$(PHP_SERVICE) php bin/console doctrine:database:create --env=test
	$(PHP_SERVICE) php bin/console doctrine:schema:create --env=test
	$(PHP_SERVICE) php bin/console doctrine:fixtures:load --env=test
	$(PHP_SERVICE) php bin/phpunit

# Limpia y reinicia los contenedores Docker
clean:
	docker-compose down --rmi all
	rm -rf var/cache var/log
	docker-compose up -d --build

# Muestra ayuda con los comandos disponibles
help:
	@echo "Comandos disponibles:"
	@echo "  make init-project            - Inicializa el proyecto"
	@echo "  make update-database-schema  - Actualiza el esquema de la base de datos"
	@echo "  make load-fixtures-data      - Carga las fixtures en la base de datos"
	@echo "  make tests                   - Ejecuta los tests del proyecto"
	@echo "  make clean                   - Limpia y reinicia el entorno"
