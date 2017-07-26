
## RV Challenge - Jonathan Robinson

I built an quick API using the PHP Laravel framework. I decided to go with laravel because it my favorite framework to work with. 
Below are setup instructions set the the db and import the test data from the original branch.

- [Laravel](https://laravel.com) PHP Framework.
- [Google Places Api][https://developers.google.com/places/web-service/] Find Locations that don't exist'.
- [Guzzle](http://docs.guzzlephp.org/en/stable/#): PHP HTTP Client.

## Set Up 
1. add homestead vagrant box
```
    vagrant box add laravel/homestead
```

2. Install Dependecies
```
    composer install
```

3. add homestead files to project
```
    php vendor/bin/homestead make
```

4. Start Server - localhost:8000
```
    vagrant up
```

5. Log On to Vagrant Server
```
    vagrant ssh
```

6. Change Directories to project Directory
```
    Cd Code/rest-api-project-jonbrobinson
```

7. Load DB With Test Data
```
    php artisan migrate:refresh
    php artisan import:data
```

## Execute Api

Base Url `localhost:8000`

### Endpoints
```
GET /state/{state}/cities
```
```
POST /user/{userId}/visits
{
    "city": "Chicago",
    "state": "IL"
}
```
```
DEL /user/{userId}/visit/{visit}
```
```
GET /user/{userId}/visits
```
```
GET /user/{userId}/visits/states
```

## Server Reqs

- Vagrant 1.9.7
- VirtualBox 5.1
- PHP >= 5.6.4
- OpenSSL
- PDO PHP Exention
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension