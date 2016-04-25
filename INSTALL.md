# INSTALLATION

## Summary 
Optional LaSalleCMS package for inbound email processing with Mailgun. 


## composer.json:

```
{
    "require": {
        "lasallecms/lasallecmsmailgun": "1.*",
    }
}
```


## Service Provider

In config/app.php:
```
Lasallecms\Shortmessageservice\LasallecmsmailgunServiceProvider::class,
```


## Facade Alias

* none


## Dependencies
* none


## Publish the Package's Config

With Artisan:
```
php artisan vendor:publish
```

## Migration

With Artisan:
```
php artisan migrate
```

## Notes

* view files will be published to the main app's view folder
* you should install all your packages first, then run "vendor:publish" and "migrate"
* run "vendor:publish" first; then, run "migrate" second


## Serious Caveat 

This package is designed to run specifically with my Flagship blog app. See my README.md for the full shpiel. 