mysql -uroot -p'root' << EOF
CREATE DATABASE isle_dev;
exit
EOF

# Disable default scotchbox site
a2dissite scotchbox.local

# Add site configuration for ISLE
cp /var/www/isle.local.conf /etc/apache2/sites-available/isle.local.conf
a2ensite isle.local

# Initialize db tables and data for each instance.
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/init.sql"
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/data.sql"
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance2/init.sql"
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance2/data.sql"

# Create apache conf for each instance.
cp /var/www/instances/myinstance/isle.local.myinstance.conf /etc/apache2/sites-available/isle.local.myinstance.conf
a2ensite isle.local.myinstance
cp /var/www/instances/myinstance2/isle.local.myinstance2.conf /etc/apache2/sites-available/isle.local.myinstance2.conf
a2ensite isle.local.myinstance2

# Create logrotate conf for each instance.
cat <<EOT >> /etc/logrotate.d/isle-myinstance
/var/www/instances/myinstance/logs/*.log {
        yearly
        maxsize 2M
        rotate 5
        notifempty
        missingok
        su vagrant vagrant
}
EOT

cat <<EOT >> /etc/logrotate.d/isle-myinstance2
/var/www/instances/myinstance2/logs/*.log {
        yearly
        maxsize 2M
        rotate 5
        notifempty
        missingok
        su vagrant vagrant
}
EOT

# Restart Apache for conf changes to take effect
service apache2 reload