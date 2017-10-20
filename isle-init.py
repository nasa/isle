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
#  - Assumes Posix-compatible shell that supports the "sort" and "uniq"
#    commands.
#  - Must be run by user root.

import os
import sys
import re
import subprocess
import MySQLdb
import getpass
import argparse

EPILOG = ""

BASE_DIR		= '/var/www/'
SECRETS_FILE		= BASE_DIR + 'webroot/isle/includes/classes/Secrets.php'
PHP_BASE_CMD		= 'php -r "@include \'' + SECRETS_FILE + "';"
LOGROTATE_FILE_PATTERN	= "/etc/logrotate.d/isle-%s"
INSTANCE_DIR_PATTERN	= BASE_DIR + "instances/%s/"
INSTANCE_NAMES		= [ 'myinstance', 'myinstance2' ]


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
    return result


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
    parser.add_argument('-D', '--DEBUG', action='store_true', default=False,
                        help='Enable debugging mode.')
    args = parser.parse_args()

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

    if not args.interactive or ask("Create ISLE's database?"):
        # Connecting to MySQL's management DB in order to create the ISLE DB:
        db = MySQLdb.connect(host = mysql_host, user = mysql_user,
                             passwd = mysql_pwd, db = "mysql" )
        cur = db.cursor()
        cur.execute("CREATE DATABASE %s;" % mysql_db_name)
        retval = cur.fetchall()
        print("CREATE DATABASE returned: %s" % retval)
        db.close()

    if not args.interactive or ask("Populate all database instances?"):
        # Connecting to the new ISLE DB:
        isle_db = MySQLdb.connect(host = mysql_host, user = mysql_user,
                                  passwd = mysql_pwd, db = mysql_db_name)
        isle_cur = isle_db.cursor()
        # Initialize DB tables and data for each instance:
        for inst in INSTANCE_NAMES:
            inst_dir = INSTANCE_DIR_PATTERN % inst
            isle_cur.execute(read_file(inst_dir + "init.sql"))
            retval = cur.fetchall()
            print("%s returned: %s" % (inst_dir + "init.sql", retval)
            isle_cur.execute(read_file(inst_dir + "data.sql"))
            retval = cur.fetchall()
            print("%s returned: %s" % (inst_dir + "data.sql", retval)

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
        else:
            print("Did not restart webserver.")

