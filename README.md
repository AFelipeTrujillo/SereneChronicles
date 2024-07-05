Serene Chronicles
=================

A personal blog project built with Symfony 7 and MySQL, inspired by Medium.

Getting Started
---------------

Follow these instructions to set up and run the project on your local machine.

### Prerequisites

*   Docker
*   Docker Compose
*   Symfony CLI

### Installation

1.  Clone the repository:

        git clone https://github.com/yourusername/serene-chronicles.git

2.  Navigate to the project directory:

        cd serene-chronicles

3.  Create the Symfony project:

        docker exec -it serene_chronicles_php-apache_1 symfony new ./ --version=7.* --webapp

4.  Build and start the Docker containers:

        docker-compose up --build


### Database Configuration

Create a `.env.local` file with the following content to configure the database connection:

    DATABASE_URL="mysql://myuser:mypassword@mysql:3306/mydatabase?serverVersion=5.7"

### Doctrine Configuration

Create the `config/packages/doctrine.yaml` file with the following content:

    doctrine:
        dbal:
            driver: 'pdo_mysql'
            server_version: '5.7'
            url: '%env(resolve:DATABASE_URL)%'
        orm:
            auto_generate_proxy_classes: true
            naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
            auto_mapping: true

### Install Dependencies

Install required dependencies:

    composer require symfony/orm-pack
    composer require --dev symfony/maker-bundle

### Generate Entities

Generate the entity for the Post model:

    php bin/console make:entity Post

Define the fields for the Post entity according to your needs.

### Create Database and Migrate

Create the database and run migrations:

    php bin/console doctrine:database:create
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate

### Fixtures

Create and load fixtures to generate sample data:

Install Faker:

    composer require fakerphp/faker --dev

Create a fixture class for the Post entity:

    // src/DataFixtures/AppFixtures.php
    namespace App\DataFixtures;
    
    use App\Entity\Post;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Persistence\ObjectManager;
    use Faker\Factory;
    
    class AppFixtures extends Fixture
    {
        public function load(ObjectManager $manager)
        {
            $faker = Factory::create();
    
            for ($i = 0; i < 10; $i++) {
                $post = new Post();
                $post->setTitle($faker->sentence);
                $post->setContent($faker->paragraph);
                $post->setCreatedAt($faker->dateTimeThisYear);
                
                $manager->persist($post);
            }
    
            $manager->flush();
        }
    }

Load the fixtures:

    php bin/console doctrine:fixtures:load

### View a Post

Create a template to view a single post:

    {% extends 'base.html.twig' %}
    
    {% block title %}{{ post.title }} | Serene Chronicles{% endblock %}
    
    {% block stylesheets %}
    
    {% endblock %}
    
    {% block body %}
    
        
            {{ post.title }}
            Published on {{ post.createdAt|date('F j, Y') }}
        
        
            {{ post.content|raw }}
        
    
    {% endblock %}

### Style the Post

Create a CSS file to style the post page:

    /* public/css/post.css */
    
    body {
        font-family: 'Georgia', serif;
        line-height: 1.6;
        background: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    
    header {
        background: #333;
        color: #fff;
        padding: 10px 0;
        text-align: center;
    }
    
    header h1 {
        margin: 0;
        font-size: 2rem;
    }
    
    header nav ul {
        list-style: none;
        padding: 0;
    }
    
    header nav ul li {
        display: inline;
        margin: 0 10px;
    }
    
    header nav ul li a {
        color: #fff;
        text-decoration: none;
    }
    
    article.post {
        background: #fff;
        margin: 20px auto;
        padding: 20px;
        max-width: 800px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .post-header {
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    
    .post-title {
        font-size: 2.5rem;
        margin: 0;
    }
    
    .post-meta {
        color: #999;
    }
    
    .post-content {
        font-size: 1.125rem;
    }

### Run the Server

Start the Symfony server:

    symfony server:start

Access the application in your browser:

    http://localhost:9090