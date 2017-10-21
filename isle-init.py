#!/usr/bin/env python
#***************************************************************************
#* Copyright 2017 Pete DiMarco
#*
#* Licensed under the Apache License, Version 2.0 (the "License");
#* you may not use this file except in compliance with the License.
#* You may obtain a copy of the License at
#*
#*     http://www.apache.org/licenses/LICENSE-2.0
#*
#* Unless required by applicable law or agreed to in writing, software
#* distributed under the License is distributed on an "AS IS" BASIS,
#* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#* See the License for the specific language governing permissions and
#* limitations under the License.
#***************************************************************************
#
# Name: isle-init.py
# Version: 0.1
# Date: 2017-10-19
# Written by: Pete DiMarco <pete.dimarco.software@gmail.com>
# Based on: isle-init.sh
#
# Description:
# Initialization script for ISLE's MySQL database.
#
# Limitations:
#  - Assumes Python 2.7 or possibly lower.
#  - Assumes Posix-compatible shell.
#  - Assumes PHP can be run from the shell to access the Secrets.php file.
#  - Must be run by user root.

import os
import sys
import re
import subprocess
import MySQLdb
import getpass
import argparse
import sqlparse

BASE_DIR		= '/var/www/'
SECRETS_FILE		= BASE_DIR + 'webroot/isle/includes/classes/Secrets.php'
PHP_BASE_CMD		= 'php -r "@include \'' + SECRETS_FILE + "';"
LOGROTATE_FILE_PATTERN	= "/etc/logrotate.d/isle-%s"
INSTANCE_DIR_PATTERN	= BASE_DIR + "instances/%s/"
INSTANCE_NAMES		= [ 'myinstance', 'myinstance2' ]
SQL_FILES		= [ 'init.sql', 'data.sql' ]

EPILOG = ""
DEBUG  = False

# \fn run_cmd
# \public
# \brief Runs the command in a shell. Returns an empty string if an exception
#        is raised.
# \param [in] cmd_str          string
# \param [in] ignore_exit_1    boolean
# \return string

def run_cmd(cmd_str, ignore_exit_1 = False):
    result = ""
    try:
        # If we have a reasonable version of Python:
        if sys.version_info >= (2,7):
            result = subprocess.check_output(cmd_str, shell=True)
        else:	# Else this machine needs an upgrade:
            fp = os.popen(cmd_str, "r")
            result = fp.read()
            fp.close()
    except Exception as e:
        # grep will return errno == 1 if it doesn't match any lines
        # in its input stream.  We want to ignore this case since it's
        # not really an error.
        if (type(e) != subprocess.CalledProcessError or e.returncode != 1 or
            not ignore_exit_1):
            print("\tThis command:")
            print(cmd_str)
            print("\tGenerated this exception:")
            print(e, str(e))
    return result


# \fn read_file
# \public
# \brief Reads the contents of a file into 1 large string.
# \param [in] file_name          string
# \return string

def read_file(file_name):
    result = ""
    if os.path.exists(file_name) and os.path.isfile(file_name):
        try:
            fp = open(file_name, "r")
            result = fp.read()
            fp.close()
        except:
            print("ERROR: Problem reading %s." % file_name)
            exit(0)
    else:
        print("ERROR: %s does not exist." % file_name)
        exit(0)
    return result.decode("utf-8-sig").encode("utf-8")		# Handle BOM.


# \fn ask
# \public
# \brief Prompts the user for a yes or no answer.
# \param [in] question          string
# \return boolean

def ask(question):
    while True:
        answer = raw_input(question + " ").lower()
        if len(answer) == 0 or answer[0] == "y":
            return True
        elif answer[0] == "n":
            return False
        else:
            print('Please enter "yes" or "no".')
            print('Just pressing <Enter> is assumed to mean "yes".')


# \fn send_cmd
# \public
# \brief Sends a command to a MySQL server.
# \param [in] cursor	cursor object
# \param [in] cmd	string
# \return None

def send_cmd(cursor, cmd):
    if not cmd:
        print("Skipping empty %s" % type(cmd))
        return

    try:
        cursor.execute(cmd)
        retval = cursor.fetchall()
        if DEBUG:
            print("%s returned: %s" % (cmd, retval if retval else 'N/A'))
    except Exception as e:
        print("\tThis command:")
        print(cmd)
        print("\tGenerated this exception:")
        print(e, str(e))


if __name__ == "__main__":
    # Parse command line arguments:
    parser = argparse.ArgumentParser(formatter_class=argparse.RawDescriptionHelpFormatter,
                                     description="ISLE installation script.",
                                     epilog=EPILOG)
    parser.add_argument('-i', '--interactive', action='store_true', default=False,
                        help='Interactive mode.')
    parser.add_argument('-r', '--systemctl', action='store_true', default=False,
                        help='Use "systemctl restart" to restart server.')
    parser.add_argument('-d', '--service', action='store_true', default=False,
                        help='Use "service reload" to restart server.')
    parser.add_argument('-c', '--clean', action='store_true', default=False,
                        help='Drop the existing database and start clean.')
    parser.add_argument('-D', '--DEBUG', action='store_true', default=False,
                        help='Enable debugging mode.')
    args = parser.parse_args()
    DEBUG = args.DEBUG

    # Check the effective user name:
    if getpass.getuser() != "root":
        print("Please run this script as root.")
        exit()

    if args.service and args.systemctl:
        print('ERROR: the "systemctl" and "service" commands are mutually exclusive.')
        exit()

    mysql_user    = run_cmd(PHP_BASE_CMD + 'echo ISLE\Secrets::DB_USER;"').strip()
    mysql_pwd     = run_cmd(PHP_BASE_CMD + 'echo ISLE\Secrets::DB_PASSWORD;"').strip()
    mysql_host    = run_cmd(PHP_BASE_CMD + 'echo ISLE\Secrets::DB_HOST_NAME;"').strip()
    mysql_port    = run_cmd(PHP_BASE_CMD + 'echo ISLE\Secrets::DB_PORT;"').strip()
    mysql_db_name = run_cmd(PHP_BASE_CMD + 'echo ISLE\Secrets::DB_NAME;"').strip()

    # Connecting to MySQL's management DB in order to create the ISLE DB:
    db = MySQLdb.connect(host = mysql_host, user = mysql_user,
                         use_unicode = True, charset = "utf8",
                         passwd = mysql_pwd, db = "mysql" )
    cur = db.cursor()
    send_cmd(cur, "SET NAMES 'utf8';")		# Turn on Unicode UTF8.

    if args.clean and ask("Are you sure you want to drop the existing ISLE database?"):
        print("Dropping existing database '%s'." % mysql_db_name)
        send_cmd(cur, "DROP DATABASE %s;" % mysql_db_name)

    if not args.interactive or ask("Create ISLE's database?"):
        send_cmd(cur, "CREATE DATABASE %s;" % mysql_db_name)

    db.close()		# Done with MySQL's management DB.

    if not args.interactive or ask("Populate all database instances?"):
        # Connecting to the new ISLE DB:
        isle_db = MySQLdb.connect(host = mysql_host, user = mysql_user,
                                  use_unicode = True, charset = "utf8",
                                  passwd = mysql_pwd, db = mysql_db_name)
        isle_cur = isle_db.cursor()
        send_cmd(isle_cur, "SET NAMES 'utf8';")	# Turn on Unicode UTF8.

        # Initialize DB tables and data for each instance:
        for inst in INSTANCE_NAMES:
            inst_dir = INSTANCE_DIR_PATTERN % inst
            # For each type of SQL file:
            for f in SQL_FILES:
                cmds = sqlparse.split(read_file(inst_dir + f))
                for cmd in cmds:
                    send_cmd(isle_cur, cmd)

        isle_db.close()

    if not args.interactive or ask("Modify logrotate files?"):
        # Create logrotate configuration for each instance:
        for inst in INSTANCE_NAMES:
            with open(LOGROTATE_FILE_PATTERN % inst, "a") as f:
                f.write("""
/var/www/instances/%s/logs/*.log {
        yearly
        maxsize 2M
        rotate 5
        notifempty
        missingok
    }
""" % inst)

    # Restart Apache for configuration changes to take effect:
    if not args.interactive or ask("Restart the webserver?"):
        if args.service:
            run_cmd("service apache2 reload")		# Debian Linux.
        elif args.systemctl:
            run_cmd("systemctl restart httpd.service")	# Red Hat linux.
        elif DEBUG:
            print("Did not restart webserver.")

