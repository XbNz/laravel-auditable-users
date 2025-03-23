![Release](https://img.shields.io/github/v/release/XbNz/laravel-auditable-users?style=for-the-badge)
![Test suite](https://img.shields.io/github/actions/workflow/status/XbNz/laravel-auditable-users/phpunit.yml?label=Tests&logo=github&style=for-the-badge)
![PHPStan](https://img.shields.io/github/actions/workflow/status/XbNz/laravel-auditable-users/phpstan.yml?label=PHPStan&logo=github&style=for-the-badge)


# Laravel Auditable Users

> [!WARNING]
> Please do not use this package if you do not have a full understanding of event sourcing. You **will** need to modify things to fit your needs. Feel free to fork or contribute when your needs outgrow this package.

> [!NOTE]
> You may use the Laravel starter kit [here]()

> [!NOTE] 
> You will need redis installed and configured to use this package

> [!NOTE]
> Livewire and a Flux Pro subscription are required  

## Features
- User registration
- User login
- Password resets
- Login tracking

## Installation

```bash
composer require xbnz/laravel-auditable-users
php artisan vendor:publish --provider="XbNz\LaravelAuditableUsers\AuditableServiceProvider"
cat "AUDITABLE_USERS_REDIRECT_AFTER_LOGIN={your_desired_route}" >> .env
cat "AUTH_MODEL=XbNz\\LaravelAuditableUsers\\Projections\\User" >> .env
php artisan migrate
```

## Usage
Ensure the routes for this package were registered successfully:
```bash
php artisan route:list
```

Then visit the /login, /register, /forgot-password routes to see the package in action.

## License
MIT

