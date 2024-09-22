FROM ghcr.io/msalehipro/laravel-octane:latest

# Create user and group, set ownership
RUN groupadd -g 1000 laravel \
    && useradd -m -g laravel -u 1000 -s /bin/sh laravel \
    && chown -R laravel:laravel /var/www \
    && usermod -u 1000 laravel \
    && groupmod -g 1000 laravel




# Install required packages
RUN apt update && apt install -y cron default-mysql-client


# Setup Supervisor
RUN mkdir -p /etc/supervisor/conf.d
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf
RUN chmod 0644 /etc/supervisor/conf.d/supervisor.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Set permissions for application files
RUN chown -R laravel:laravel /var/www/html

# Install PHP dependencies and set up Laravel
RUN composer install
RUN php artisan key:generate
RUN php artisan storage:link

# Setup cron job
COPY docker/cron /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab

# Ensure the start script has the correct permissions
RUN chown laravel:laravel /var/www/html/docker/start.sh
RUN chmod +x /var/www/html/docker/start.sh


RUN chown -R laravel:laravel /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage

CMD ["chmod", "+x", "./docker/start.sh"]

# Use ENTRYPOINT for the startup script
ENTRYPOINT ["/var/www/html/docker/start.sh"]
