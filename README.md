#  CRUD API with Authentication

This is a simple REST API for a blog post management system. Implemented authentication using JWT (Json Web Token). The API
allows basic CRUD (Create, Read, Update, Delete) operations on “Post” entities.

There are functional tests implemented for every endpoint.  
Latest PHP and Symfony versions at the moment.  
Following PSR-12.

### Prerequisites

Ensure you have the following installed on your machine:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Clone the Repository

First, clone the project from GitHub:

```bash
git clone https://github.com/giezele/auth-crud-api
cd auth-crud-api
```

### Environment Configuration

Duplicate the `.env.example` file to create a `.env` file:

```bash
cp .env.example .env
```

Make any necessary changes to the environment variables in the `.env` file. For instance, update database credentials or other configurations.

### Docker Setup

Build and start the Docker containers using Docker Compose:

```bash
docker-compose up --build -d
```

This will build the Docker containers for the application and start them in the background.

### Install Dependencies

Once the containers are up, you need to install the PHP dependencies via Composer. Run the following command inside the PHP container:

```bash
docker exec -it symfony_php composer install
```

### Database Setup

After the dependencies are installed, you need to set up the database. Run the following commands to create the database and apply migrations:

```bash
# Create the database
docker exec -it symfony_php php bin/console doctrine:database:create --if-not-exists --env=dev

# Run the migrations
docker exec -it symfony_php php bin/console doctrine:migrations:migrate --no-interaction
```

### Running the Application

The application should now be accessible. You can check it by navigating to `http://localhost` in your browser (or the port specified in your Docker Compose setup).

### Running Tests

To ensure everything is working properly, you can run the functional tests:

```bash
docker exec -it symfony_php php bin/phpunit
```

### Stopping the Containers

When you're done working with the project, you can stop the Docker containers with:

```bash
docker-compose down
```

### Common Commands

- **Access the PHP container**:
  ```bash
  docker exec -it symfony_php bash
  ```

- **Clear the Symfony cache**:
  ```bash
  docker exec -it symfony_php php bin/console cache:clear
  ```

- **Run database migrations**:
  ```bash
  docker exec -it symfony_php php bin/console doctrine:migrations:migrate
  ```

### Troubleshooting

- **Database Issues**: If you encounter database connection issues, ensure that the database configuration in the `.env` file matches the settings for the Docker container (`DB_HOST=db`, `DB_PORT=3306`).
- **Container Logs**: To view the logs of a container, use:
  ```bash
  docker logs symfony_php
  ```


