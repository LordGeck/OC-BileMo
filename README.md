# OC-BileMo
[![Maintainability](https://api.codeclimate.com/v1/badges/8ab398cbd1c5ca584e43/maintainability)](https://codeclimate.com/github/LordGeck/OC-BileMo/maintainability)

API REST d'un site de vente faite dans le cadre de la formation Openclassroom Développeur d'application - PHP / Symfony

## Information

Technologies utilisées
* Symfony 5.3
* Php 8.0.0
* Jwt-authentication-bundle 2.12.6
* Hateoas-bundle 2.2.0
* NelmioApiDocBundle 4.6
* Composer 1.11.99

## Installer le projet 

* Cloner le projet
* Installer composer
* Installer les dépendances via composer
* Editer les variable d'environnement dans le .env
* * Imformation de connection à la base de donnée
* * Générer la clé SSL necessaire au fonctionnement de JWT :
* * ```php bin/console lexik:jwt:generate-keypair```
* Construire la base de donnée via doctrine
* Installer le jeu de données via les fixtures
