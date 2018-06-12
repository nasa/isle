#!/bin/bash

select opt in "Dump" "Load" "Cancel"; do
    case $opt in
        Dump ) sudo bash /var/www/mysqldump.sh; break;;
        Load ) sudo bash /var/www/mysqlload.sh; break;;
        Cancel ) exit;;
    esac
done

