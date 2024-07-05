README - Serene Chronicles
==========================

Getting Started
---------------

To start using Serene Chronicles, follow these steps:

### 1\. Clone the Repository

    git clone https://github.com/tuusuario/serene-chronicles.git

### 2\. Navigate to the Project Directory

    cd serene-chronicles

### 3\. Create Symfony Project

Create a new Symfony project using Symfony CLI:

    symfony new ./ --version=7.* --webapp

This command creates a new Symfony project with the necessary structure, including assets and a default web server configuration.

### 4\. Build and Start Docker Containers

If not already done, build and start the Docker containers:

    docker-compose up --build

This command builds the necessary Docker images and starts the containers defined in `docker-compose.yml`.

### 5\. Access the Application

Access your application in the browser at `http://localhost:9090`.