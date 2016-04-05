mysql -uroot -p'root' << EOF
DROP DATABASE isle_dev;
CREATE DATABASE isle_dev;
exit
EOF

mysql -uroot -p'root' -h localhost isle_dev < "/var/www/db_backup.sql"
