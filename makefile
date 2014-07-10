all: build

build: community enterprise enterprise-vip

community: livefyre-comments/*
	./build.sh -c

enterprise: livefyre-comments/*
	./build.sh -m -e

clean:
	rm build
