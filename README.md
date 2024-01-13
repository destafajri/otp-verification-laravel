<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About The Project
This project serves as a comprehensive example of a user verification process utilizing OTP (One-Time Password). The OTPs are securely stored in Redis and simultaneously dispatched to the user's email through [Mailtrap](https://mailtrap.io/home). The primary databases employed are MySQL, complemented by Redis for queuing jobs, i already prepare it on docker-compose.yml

## Coding Flow
The code follows a clean and modular architecture, divided into three layers:
```
Controller -> Service -> Repository
```

To facilitate the creation of service and repository layers, the following commands and stubs have been included:

- To Create Service layer
Use the following Artisan command to generate a new service:
```
php artisan make:service {{ServiceName}}
```

- To Create Repository layer
Generate a repository with the following Artisan command:
```
php artisan make:repository {{RepositoryName}}
```