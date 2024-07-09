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

Project Setup
-------------

We have configured our project to use Docker for the environment setup and Symfony for the application framework. Here are the main configurations and steps we followed:

### Docker Configuration

Our Docker setup includes a PHP-Apache container and a MySQL container. Here is the `docker-compose.yml` file:


    version: '3.8'
    
    services:
      php-apache:
        build:
          context: .
          dockerfile: Dockerfile
        volumes:
          - ./src/:/var/www/html/
        ports:
          - "9090:80"
          - "9091:8000"
        environment:
          - APACHE_RUN_USER=www-data
          - APACHE_RUN_GROUP=www-data
          - DATABASE_URL=mysql://myuser:mypassword@mysql:3306/mydatabase
        networks:
          - app-network
    
      mysql:
        image: mysql:latest
        environment:
          MYSQL_DATABASE: mydatabase
          MYSQL_USER: myuser
          MYSQL_PASSWORD: mypassword
          MYSQL_ROOT_PASSWORD: rootpassword
        volumes:
          - mysql_data:/var/lib/mysql
        networks:
          - app-network
    
    volumes:
      mysql_data:
    
    networks:
      app-network:
        driver: bridge


### Dockerfile Configuration

Our Dockerfile is set up to install all necessary dependencies, including PHP extensions, Node.js, and Symfony CLI. Here is the `Dockerfile`:


    # Use official PHP image with Apache
    FROM php:8.2-apache
    
    # Install necessary dependencies to build extensions and librabbitmq
    RUN apt-get update && apt-get install -y \
        libicu-dev \
        libpq-dev \
        libxslt-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libssl-dev \
        libsodium-dev \
        libssh-dev \
        libmagickwand-dev \
        libmagickcore-dev \
        wget \
        unzip \
        git \
        pkg-config \
        librabbitmq-dev \
        curl \
        libmariadb-dev-compat \
        libmariadb-dev
    
    # Install PHP extensions
    RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install \
        intl \
        pdo_mysql \
        xsl \
        gd \
        opcache \
        sodium \
        bcmath
    
    # Install AMQP extension
    RUN pecl install amqp \
        && docker-php-ext-enable amqp
    
    # Install Composer
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    
    # Enable Apache rewrite module
    RUN a2enmod rewrite
    
    # Install Node.js and npm
    RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
        && apt-get install -y nodejs
    
    # Install Symfony CLI
    RUN wget https://get.symfony.com/cli/installer -O - | bash \
        && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony
    
    # Configure Apache to point to /var/www/html/public directory
    RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
    RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf
    
    # Expose port 80
    EXPOSE 80
    
    # Set working directory
    WORKDIR /var/www/html
    
    # Copy application source code into the container
    COPY . /var/www/html
    
    # Optionally grant permissions to www-data user
    # RUN chown -R www-data:www-data /var/www/html
    
    # Run Apache in foreground
    CMD ["apache2-foreground"]


### Setting Up Database

We configured Doctrine to use MySQL. Here is the `doctrine.yaml` configuration file:


    doctrine:
        dbal:
            url: '%env(resolve:DATABASE_URL)%'
            driver: 'pdo_mysql'
            charset: utf8mb4
        orm:
            auto_generate_proxy_classes: true
            naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
            auto_mapping: true
            mappings:
                App:
                    is_bundle: false
                    type: annotation
                    dir: '%kernel.project_dir%/src/Entity'
                    prefix: 'App\Entity'
                    alias: App


### Installing Required Packages

We installed the required packages using Composer:

    composer require symfony/orm-pack

    composer require --dev symfony/maker-bundle

Implementing Core Features
--------------------------

### Creating and Managing Posts

We created the `Post` entity and the corresponding form type. We also set up validation for unique slugs:


    entityManager = $entityManager;
        }
    
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('title')
                ->add('content')
                ->add('slug')
            ;
    
            $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $post = $event->getData();
    
                $existingPost = $this->entityManager->getRepository(Post::class)->findOneBy(['slug' => $post->getSlug()]);
    
                if ($existingPost && $existingPost->getId() !== $post->getId()) {
                    $form->get('slug')->addError(new FormError('The slug is already in use.'));
                }
            });
        }
    
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Post::class,
            ]);
        }
    }


### Adding Slug Field to Existing Posts

We added a slug field to the `Post` entity and created a migration to update existing posts.

    php bin/console make:migration

    php bin/console doctrine:migrations:migrate

### Post Templates

We created templates for displaying the list of posts and individual post details.

Here is the `post/index.html.twig`:


    {# templates/post/index.html.twig #}
    {% extends 'base.html.twig' %}
    
    {% block title %}Posts | Serene Chronicles{% endblock %}
    
    {% block stylesheets %}
        
    {% endblock %}
    
    {% block body %}
        
            Recent Posts
            
                
                Search
            
            {% for post in pagination.items %}
                
                    {{ post.title }}
                    Published on {{ post.createdAt|date('F j, Y') }}
                    {{ post.content|raw|slice(0, 200) }}...
                    Read more
                
            {% endfor %}
        
    
        
            {{ knp_pagination_render(pagination) }}
        
    {% endblock %}


And here is the `post/show.html.twig` with the edit button added:


    {% extends 'base.html.twig' %}
    
    {% block stylesheets %}
        
    {% endblock %}
    
    {% block title %}{{ post.title }} | Serene Chronicles{% endblock %}
    
    {% block body %}
        
            
                {{ post.title }}
                Published on {{ post.createdAt|date('F j, Y') }}
            
            
                {{ post.content|raw }}
            
            Edit Post
        
    {% endblock %}


### Adding Search Functionality

We added a search form to the index page to search posts by title or content. Here is the updated `PostRepository` with the search functionality:


    public function findByTitleOrContent(string $searchTerm) : \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :searchTerm')
            ->orWhere('p.content LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.created_at', 'DESC')
            ->getQuery()
        ;
    }


We also added pagination to the post listing using KnpPaginatorBundle. Here is the updated index action in the `PostController`:


    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search', '');
    
        $query = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findByTitleOrContent($searchTerm);
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
    
        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }