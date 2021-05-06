# Usage: $ make

APT := $(shell eval command -v apt)

.PHONY: help

help:  ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install:  ## Run this after a fresh clone
	@echo "Installing PHP dependencies…"
	composer install
	@echo "Installing gherkin feature suite…"
	git submodule update --init --recursive
	@echo "Initializing feature suite…"
	cp --no-clobber behat.yml.dist behat.yml
	@echo "Initializing cryptography…"
	@bash bin/setup_jwt.bash
ifdef APT
	@echo "Installing optional debian packages for extra fun…"
	sudo apt install -y fortunes cowsay
endif
	@echo "Running the feature suite…"
	vendor/bin/behat

inspection:  ## Run all the tests
	vendor/bin/behat -vv

inspection-automated:  ## Run the subset of tests suitable for Continuous Integration
	vendor/bin/behat -vv --tags="~wip&&~noci"

cuisine:  ## Run the subset of Work In Progress tests
	vendor/bin/behat -vv --tags="wip"

client-typescript-node:  ## Generate in /tmp a client library that can consume our API
	bash bin/generate-client.bash typescript-node

client-php:  ## Generate in /tmp a client library that can consume our API
	bash bin/generate-client.bash php
