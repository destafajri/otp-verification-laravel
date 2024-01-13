<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About The Project
This Project is an example process for user verification using otp, the otp is store on redis and also send it to email using [mailtrap](https://mailtrap.io/home), the main database is used MySQL and redis to for queue job, i already prepare it on docker-compose.yml

## Coding Flow
```
Controller -> Service -> Repository
```

To create  service layer and repository layer, i adding some command and stubs

- To Create Service layer
```
php artisan make:service {{ ServiceName }}
```

- To Create Repository layer
```
php artisan make:repository {{ RepositoryName }}
```