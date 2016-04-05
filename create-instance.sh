echo "Type the name of the instance you wish to create, followed by [ENTER] (ex. isle.local/INSTANCE_NAME):"

read instance

# copy sql files into new folder.
# Initialize db tables and data for each instance.
#mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/init.sql"
#mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/data.sql"

cp /var/www/isle.local.myinstance.conf /etc/apache2/sites-available/isle.local.myinstance.conf
a2dissite scotchbox.local
a2ensite isle.local
a2ensite isle.local.myinstance

service apache2 reload