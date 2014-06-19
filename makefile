all: build

build: community enterprise

community: livefyre-comments/*
	./build.sh -c

enterprise: livefyre-comments/*
	./build.sh -m -e

clean:
	rm build
