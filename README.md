It's a demo of video streaming app (something like youtube) that I wrote to expand my knowledge of symfony.

Installation
======

Run these commands inside a main directory (you need composer, npm and gulp):

```
composer update
npm install
bin/console doctrine:schema:update
gulp
```

If you want to run tests make sure to load fixtures first by running this command:

```
bin/console doctrine:fixtures:load
```

To run tests run:

```
phpunit
```
