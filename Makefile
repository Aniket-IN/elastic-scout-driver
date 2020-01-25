.PHONY: up down wait test help

.DEFAULT_GOAL := help

## mysql config
MYSQL_CONTAINER_IMAGE := mysql:5.6
MYSQL_CONTAINER_NAME := elastic-scout-driver-mysql
MYSQL_HOST_PORT := 13306
MYSQL_DATABASE := test
MYSQL_USER := test
MYSQL_PASSWORD := test

## elasticsearch config
ES_CONTAINER_IMAGE := elasticsearch:7.5.0
ES_CONTAINER_NAME := elastic-scout-driver-elasticsearch
ES_HOST_PORT := 19200
ES_DISCOVERY_TYPE := single-node

up: ## Start containers
	@echo "→ Starting ${MYSQL_CONTAINER_NAME} container:"
	@docker run --rm -d \
		--name ${MYSQL_CONTAINER_NAME} \
		-p ${MYSQL_HOST_PORT}:3306 \
		-e MYSQL_RANDOM_ROOT_PASSWORD=yes \
		-e MYSQL_DATABASE=${MYSQL_DATABASE} \
		-e MYSQL_USER=${MYSQL_USER} \
		-e MYSQL_PASSWORD=${MYSQL_PASSWORD} \
		${MYSQL_CONTAINER_IMAGE}

	@echo "→ Starting $(ES_CONTAINER_NAME) container:"
	@docker run --rm -d \
    		--name ${ES_CONTAINER_NAME} \
    		-p ${ES_HOST_PORT}:9200 \
    		-e discovery.type=${ES_DISCOVERY_TYPE} \
    		${ES_CONTAINER_IMAGE}

down: ## Stop containers
	@echo "→ Stopping containers:"
	@docker stop \
		${MYSQL_CONTAINER_NAME} \
		${ES_CONTAINER_NAME}

wait: ## Wait until container are ready
	@echo "→ Waiting for ports to be open:"
	@for port in ${MYSQL_HOST_PORT} ${ES_HOST_PORT}; do \
		until nc -z 127.0.0.1 $${port}; do \
			echo "Waiting for $${port}"; sleep 1; \
		done \
	done

test: ## Run tests
	@echo "→ Running tests:"
	@bin/phpunit --testdox

up-n-test: up wait test ## Start containers and run tests

help: ## Show help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
