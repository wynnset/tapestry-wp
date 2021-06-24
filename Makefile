start:
	make start-wp
	make start-app

start-wp:
	docker-compose up -d

start-wp-fixtures:
	docker-compose --env-file ./fixtures/.env up -d

start-app:
	cd templates/vue && npm start

start-fixtures:
	make start-wp-fixtures
	make start-app

init:
	make start-wp
	bash ./bin/await-containers.sh
	make install
	make env
	@echo "All done!"

install:
	make install-wp
	make install-app

install-wp:
	sh ./bin/install.sh

install-app:
	sh ./bin/node-install.sh

env:
	bash ./bin/setup-env.sh

clear-db:
	docker volume rm tapestry-wp_db_data

uninstall:
	make stop
	make clear-db
	cd templates/vue && rm -r node_modules

stop:
	make stop-app
	make stop-wp

stop-wp:
	docker-compose down

stop-app:
	sh ./bin/stop-app.sh
	
