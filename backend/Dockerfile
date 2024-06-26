# Attendance System Backend
FROM php:8.2-apache

# enable htaccess Rewrite
# RUN a2enmod rewrite

# Install cron
# commenting these out until cron, syncing, and photo-zipping is ready
# RUN ["apt-get", "update"]
# RUN ["apt-get", "-y", "install", "cron", "libzip-dev", "zip"]

# install PHP DB connectors (PDO, MySQLi)
# commenting these out until cron, syncing, and photo-zipping is ready
# RUN docker-php-ext-install mysqli pdo pdo_mysql zip
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

# Copy cron file to the cron.d directory
# COPY cronjobs/sync-server /etc/cron.d/sync-server

# Give execution rights on the cron job
# RUN chmod 0644 /etc/cron.d/sync-server

# Apply cron job
# RUN crontab /etc/cron.d/sync-server

# Run the command on container startup
#CMD cron && tail -f /var/log/cron.log

EXPOSE 80
# CMD cron && apache2-foreground
CMD apache2-foreground

# ADD . /var/www/html
