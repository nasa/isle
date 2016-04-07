ISLE - Inventory System for Lab Equipment
==========

![Demo](https://cloud.githubusercontent.com/assets/1322063/14293284/efe856d2-fb39-11e5-9765-7605555b06f8.gif)

## Created By
[Brandon Ruffridge](https://github.com/bruffridge)  
[Brent Gardner](https://github.com/bggardner)

## Brief Abstract
This web application allows inventories of assets to be managed. Assets along with their specifications are added to the system and then used by users via a check-in/check-out function. The inventory can be browsed by category or using search. Users are given various roles within the system to allow them to perform certain functions such as view-only, check-in/check-out, modify, and full-control. Inventory managers can add and track detailed information on all inventory assets including manufacturer, storage location, custom attributes, and relationships with other assets. Assets can be found by browsing by category, search, location, or current user. Assets are checked out to specified locations by users.

## Description of the Problem That Motivated ISLE's Development
One of our labs at the NASA Glenn Research Center wanted a way to track their inventory of over 350 pieces of equipment, who is using it, and where it is located. They also wanted to give lab users a way to see what equipment is available and see detailed specs on the equipment and check it out for use with their projects. This web based tool was developed to meet that objective.

## Technical Description

Developed using web standards and best practices such as Model-View-Controller architecture, Separation of Concerns, and Don't Repeat Yourself.  
Fast, intuitive UI featuring a custom application layout built using parts from Twitter Bootstrap, extensive AJAX and jQuery, and combined and minified Javascript and LESS CSS.  
Secure and 508 compliant.  
Features an innovative built-in bug reporting system to Pivotal Tracker.  
Deployed on the mature, open-source Linux, Apache, MySQL, and PHP (LAMP) technology stack.

## Get Started

* Have a Mac or Linux Box
  * Windows can run ISLE but can't run the bash scripts for [building static files](#building-static-files) or [syncing database for multiple developer teams](#keeping-database-in-sync-for-multiple-developer-teams)
* Have a webserver running PHP and MySQL.
  * You can also use the included Vagrant LAMP Box to deploy and run the application locally by following the steps below.
* Download and Install [Vagrant][1]
* Download and Install [VirtualBox][2]
* Clone ISLE ```git clone https://github.com/nasa/isle.git```
* Run ``` vagrant up ```
* Access Your Project at  [http://192.168.33.10/myinstance][3] or [http://192.168.33.10/myinstance2][11]

## Configuration

Search source code for ```config-todo:``` for things you may need to configure.  
**NOTE:** ISLE was modified to be easy to install and configure in a local development environment. Additional configuration steps would be needed to run ISLE in a secure production environment such as changing database credentials and moving them into a separate file and adding that file to .gitignore so the credentials don't go into source control.

## How to Contribute

[Check out our backlog](https://www.pivotaltracker.com/n/projects/1569431) of the things we want to add/fix. Fork the project, make your changes, test ( we don't have time to test for you ), then submit a pull request. Submit any new bugs or feature requests to the [issues page](https://github.com/nasa/isle/issues).

## Multiple Inventories

ISLE supports multiple "instances" so multiple inventories can be managed separately. Each instance has a unique url, but accesses the same php files and database. Data is kept separate by using different tables.  
The ```instances``` folder contains two example instances ```myinstance``` and ```myinstance2```.  
### It would be nice to have a bash script to automate creation of additional instances, however it is currently a manual process.  
To create additional instances duplicate the ```instances/myinstance``` folder and rename to whatever you want to call your instance. Delete the .log files in ```logs```. Then replace ```myinstance``` with whatever instance name you chose in all files within the duplicated folder. Also rename ```isle.local.myinstance.conf``` to ```isle.local.INSERT_YOUR_INSTANCE_NAME.conf```.  
Duplicate ```webroot/myinstance```. Delete any files in ```uploads``` except the .htaccess files.  
Edit ```isle-init.sh``` and copy and paste the following lines for running sql and enabling conf files. Replace ```myinstance``` with whatever instance name you chose.

```bash
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/init.sql"
mysql -uroot -p'root' -h localhost isle_dev < "/var/www/instances/myinstance/data.sql"

cp /var/www/instances/myinstance/isle.local.myinstance.conf /etc/apache2/sites-available/isle.local.myinstance.conf
a2ensite isle.local.myinstance

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
```

Then run the following so the changes take effect.

```bash
vagrant destroy
vagrant up
```  

## Building Static Files

When you want to make changes to CSS or JS the files you want to edit are located in:  
**JS:** ```webroot/isle/cdn/scripts-dev```  
**CSS:** ```webroot/isle/cdn/styles/less```

Don't edit files in ```scripts``` or ```css-dev``` as those are created during the build process.

Build CSS and JS (combines and minifies)
* Make sure you have lessc installed.
* ``` cd PROJECT_FOLDER/webroot/isle/includes``` (this step is important or the build files will not be saved to the correct location)
* ```./build.sh```

## Keeping Database in Sync for Multiple Developer Teams

Change Workflow:  
* Make changes.
* ```./dbup.sh``` (option 1)
* git add, git commit, git push.

Update Workflow:  
* git pull
* ```./dbup.sh``` (option 2)

## Basic Vagrant Commands


### Start or resume your server
```bash
vagrant up
```

### Pause your server
```bash
vagrant suspend
```

### Delete your server
```bash
vagrant destroy
```

### SSH into your server
```bash
vagrant ssh
```



## Database Access

### MySQL 

- Hostname: localhost or 127.0.0.1
- Username: root
- Password: root
- Database: isle_dev

## Updating the Box

Although not necessary, if you want to check for updates, just type:

```bash
vagrant box outdated
```

It will tell you if you are running the latest version or not, of the box. If it says you aren't, simply run:

```bash
vagrant box update
```


## Setting a Hostname

If you're like me, you prefer to develop at a domain name versus an IP address. If you want to get rid of the some-what ugly IP address, just add a record like the following example to your computer's host file.

```bash
192.168.33.10 isle.local
```

Or if you want "www" to work as well, do:

```bash
192.168.33.10 isle.local www.isle.local
```

Technically you could also use a Vagrant Plugin like [Vagrant Hostmanager][4] to automatically update your host file when you run Vagrant Up. However, the purpose of Scotch Box is to have as little dependencies as possible so that it's always working when you run "vagrant up".

## Special Thanks To

* [Scotch Box][5]
* [Require.js][6]
* [Bootstrap][7]
* [jQuery][8]
* [Modernizr][9]
* [tag-it][10]
* [jquery-trap-input](https://github.com/julienw/jquery-trap-input)
* [jquery-dateFormat](https://github.com/phstc/jquery-dateFormat)
* [AppLayout](https://github.com/bruffridge/AppLayout)

 [1]: https://www.vagrantup.com/downloads.html
 [2]: https://www.virtualbox.org/wiki/Downloads
 [3]: http://192.168.33.10/myinstance
 [4]: https://github.com/smdahlen/vagrant-hostmanager
 [5]: https://box.scotch.io/
 [6]: http://requirejs.org/
 [7]: http://getbootstrap.com/
 [8]: https://jquery.com/
 [9]: https://modernizr.com/
 [10]: https://github.com/aehlke/tag-it
 [11]: http://192.168.33.10/myinstance2
