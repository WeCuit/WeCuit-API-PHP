# 暂时用不到
ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf &
chown -R www-data:www-data /var/www/html &
apache2-foreground