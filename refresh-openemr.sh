#!/bin/bash
#
# Copy the new openemr version to the web directory 
# does not touch sites, globals.php or database

GITDIR=/home/growlingflea/git/santiago-peds # source

SITEDIR=/var/www/merge-412p7
 #destination


# GITDIR=/Users/kchapple/Dev/openemr
# GITDIR=/Users/kchapple/Dev/openemr/mi2-openemr
USRGRP=www-data

echo Update "${GITDIR}" to "${SITEDIR}"
echo Continue?
read X

echo Starting...

sudo rm -rf ${SITEDIR}/interface/main/calendar/modules/PostCalendar/pntemplates/compiled/*
sudo rm -rf ${SITEDIR}/interface/main/calendar/modules/PostCalendar/pntemplates/cache/*
sudo rm -rf ${SITEDIR}/gacl/admin/templates_c/*

sudo rsync -i --recursive --exclude .git --exclude interface/globals.php --exclude sites ${GITDIR}/* ${SITEDIR}/

# modify permissions
sudo chmod 666 ${SITEDIR}/sites/default/sqlconf.php
sudo chown -Rv ${USRGRP} ${SITEDIR}/library/freeb
sudo chown -Rv ${USRGRP} ${SITEDIR}/interface/main/calendar/modules/PostCalendar/pntemplates/compiled
sudo chown -Rv ${USRGRP} ${SITEDIR}/interface/main/calendar/modules/PostCalendar/pntemplates/cache
sudo chown -Rv ${USRGRP} ${SITEDIR}/gacl/admin/templates_c

