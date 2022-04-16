UNAME_SYSTEM := $(shell uname -s)

#~y
#~ Project Setup
#~ ________________________________________________________________
#~r

install:				#~ Install Inescoin dev projects
install: clone build start-node

dev:					#~ Start to dev quickly
dev: start start-node

clone:				   	#~ Clone all repositories
	[ ! -d '../inescoin-wallet' ] && git clone git@github.com:inescoin/inescoin-wallet ../inescoin-wallet || true
	[ ! -d '../inescoin-website-viewer' ] && git clone git@github.com:inescoin/inescoin-website-viewer ../inescoin-website-viewer || true
	[ ! -d '../inescoin-website-ng-checkout' ] && git clone git@github.com:inescoin/inescoin-website-ng-checkout ../inescoin-website-ng-checkout || true
	[ ! -d '../inescoin-ionic' ] && git clone git@github.com:inescoin/inescoin-ionic ../inescoin-ionic || true
	[ ! -d '../inescoin-ansible' ] && git clone git@github.com:inescoin/inescoin-ansible ../inescoin-ansible || true
	[ ! -d '../inescoin-explorer' ] && git clone git@github.com:inescoin/inescoin-explorer ../inescoin-explorer || true

ifeq ($(UNAME_SYSTEM),Darwin)
	sed -i '' -e 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' "../inescoin-explorer/src/app/App.php" #~ Replaced for dev env (never push it)
	sed -i '' -e 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' "../inescoin-website-viewer/src/app/App.php" #~ Replaced for dev env (never push it)
	sed -i '' -e 's/37\.187\.115\.92/0\.0\.0\.0/g' "./bin/inescoin-node" #~ Replaced for dev env (never push it)
else
	sed -i 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' "../inescoin-explorer/src/app/App.php" #~ Replaced for dev env (never push it)
	sed -i 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' "../inescoin-website-viewer/src/app/App.php" #~ Replaced for dev env (never push it)
	sed -i 's/37\.187\.115\.92/0\.0\.0\.0/g' "./bin/inescoin-node" #~ Replaced for dev env (never push it)
endif

build:                 	#~ Build all containers
build: clone
	docker-compose up --build -d --remove-orphans

	docker exec -it inescoin-node bash -c "cd /opt/ && composer update"
	docker exec -it inescoin-explorer-phpfpm bash -c "cd /www/ && composer update"
	docker exec -it inescoin-website-viewer-phpfpm bash -c "cd /www/ && composer update"

start:                 	#~ Start the docker containers
start: stop
	docker-compose up -d --remove-orphans

stop:                  	#~ Stop the docker containers
	docker-compose stop

console:
	docker exec -it inescoin-node bash

start-node:        		#~ Sart inescoin node
	open http://localhost:8087/status
	open http://localhost:8001/
	open http://localhost:8000/
	docker exec -it inescoin-node bash -c "bin/inescoin-node --prefix=hello"

start-miner:        	#~ Sart inescoin miner
	docker exec -it inescoin-node bash -c "bin/inescoin-miner --wallet-address=0x460fdA7C610580e319E325e0274d1dFA43B3F9c7"
