#  CRUD API with Authentication

This is a simple REST API for a blog post management system. Implemented authentication using JWT (Json Web Token). The API
allows basic CRUD (Create, Read, Update, Delete) operations on “Post” entities.

There are functional tests implemented for every endpoint (current line code coverage 95% ;) )

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

Once the containers are up, you need to install the PHP dependencies via Composer.

```bash
docker exec -it symfony_php composer install
docker exec -it symfony_php composer generate-keys
```

### Database Setup

After the dependencies are installed, you need to set up the database. Run the following commands to create the database and apply migrations:

```bash
# Create the database
docker exec -it symfony_php php bin/console doctrine:database:create --if-not-exists --env=dev

# Run the migrations
docker exec -it symfony_php php bin/console doctrine:migrations:migrate --no-interaction
```
### Update permissions
```
docker exec -it symfony_php chmod 644 config/jwt/private.pem
```

### Testing the API with Postman

**1. Create a New Request**: Select the desired HTTP method (GET, POST, PUT, DELETE).
   Example endpoints to test:


   - GET `http://localhost/api/posts` to retrieve all posts.

   - POST `http://localhost/api/posts` to create a new post.

   - GET `http://localhost/api/posts/{id}` to retrieve a specific post.

   - PUT `http://localhost/api/posts/{id}` to update an existing post.

   - DELETE `http://localhost/api/posts/{id}` to delete a post.

   - POST `http://localhost//api/login` - login to receive a JWT token.
   

**2. Add Headers**

- For authenticated endpoints, add the Authorization header with a Bearer token:
```bash
  Key: Authorization
  Value: Bearer <your-token>
```

  - For requests with JSON payloads, add the Content-Type header:

```bash
  Key: Content-Type
  Value: application/json
```


**3. Add Body** (for POST/PUT): Use JSON format.
```json
  {
    "title": "My Test Post",
    "content": "This is the content of the test post."
  }
```

**4. Send the Request:** Verify the response status and content.

- For a successful POST request to create a new post, you should get a status code of 201 Created along with the newly created resource's details in the response.

### Running Tests

To ensure everything is working properly, you can run the functional tests. You need to explicitly create and migrate the test database (`symfony_test`) before running the PHPUnit tests. 
```bash
docker exec -it symfony_php php bin/console doctrine:database:create --env=test --if-not-exists
docker exec -it symfony_php php bin/console doctrine:migrations:migrate --env=test --no-interaction
```
In case your `symfony` user has no sufficient privileges to modify the test database, check Troubleshooting.

After performing these steps, you should be able to run your PHPUnit tests
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

- **Test Database Issues** If you encounter a permission error when trying to create the test database, you may need to grant additional privileges to the MySQL user. To do this, run the following commands inside the MySQL container:
  - Enter the MySQL container:
  
      ```bash
      docker exec -it symfony_test_db mysql -u root -p
      ```
  - Grant privileges:
  
      ```sql
      GRANT ALL PRIVILEGES ON symfony_test.* TO 'symfony'@'%';
      FLUSH PRIVILEGES;
      ```
    
- **Database Issues**: If you encounter database connection issues, ensure that the database configuration in the `.env` file matches the settings for the Docker container (`DB_HOST=db`, `DB_PORT=3306`).

- **Container Logs**: To view the logs of a container, use:
    ```bash
    docker logs symfony_php
    ```


