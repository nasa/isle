select opt in "Dump" "Load" "Cancel"; do
    case $opt in
        Dump ) vagrant ssh -c "sudo /var/www/mysqldump.sh"; break;;
        Load ) vagrant ssh -c "sudo /var/www/mysqlload.sh"; break;;
        Cancel ) exit;;
    esac
done
