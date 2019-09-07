## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them?

- [Docker CE](https://www.docker.com/community-edition)
- [Docker Compose](https://docs.docker.com/compose/install)

### Install

- (optional) Create your `docker-compose.override.yml` file

```bash
cp docker-compose.override.yml.dist docker-compose.override.yml
```
> Notice : Check the file content. If other containers use the same ports, change yours.

#### Init

```bash
cp .env.dist .env
docker-compose up -d
docker-compose exec web composer install
docker-compose exec web php bin/console d:s:u --force
docker-compose exec web php bin/console h:f:l --purge-with_truncate
```

### Functionalities

-Symfony Command to create an Admin User
```bash
docker-compose exec web php bin/console app:create-admin <email> <password>
```
-Symfony Command to display the number of Card by User email
```bash
docker-compose exec web php bin/console app:cards-by-user <email>
```

#### As ANONYMOUS​ :
(localhost/api/users/ ...)
- Create a User ( with an existing name subsription - localhost/api/anonymous/users POST )

- Get One or All Subscriptions (localhost/api/anonymous/users GET or localhost/api/anonymous/users/email)

- Get One or All User (but with another serialization : User#​firstname​, User#​email​, Card#​name​ and fullSubscription informations

#### As ROLE_USER​ :
- Get my User information (profile, with full serialization information) - localhost/api/user GET or PATCH)

- Edit your User firstname, lastname, address, country, cards, subscription

- GetOne, GetAll, Edit, Create or Delete one of your Cards

#### As ROLE_ADMIN​ (​same as ROLE_USER, plus​) :

(localhost/api/admin/ ...)
##### User


- List all Users; Get one User, Edit any User; Delete any User (​with his Cards​)


##### Subscription


- Create, GetOne, GetAll, Edit, Delete


- Do not allow to delete a Subscription if there is at least one User linked. Make sure there is anerror.


##### Card


- Create, GetOne, GetAll, Edit, Delete
