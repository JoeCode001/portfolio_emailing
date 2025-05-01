# Use an official PHP image with Apache
FROM php:8.2-apache  

# Enable required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql  

# Set working directory
WORKDIR /var/www/html

# Download and unzip the backend.zip from GitHub
ADD backend.zip /var/www/html/
RUN apt-get update && apt-get install -y unzip \
    && unzip backend.zip -d /var/www/html/ \
    && rm backend.zip

# Ensure correct permissions
RUN chmod -R 755 /var/www/html/

# Expose port 80
EXPOSE 80  

# Start Apache server
CMD ["apache2-foreground"]