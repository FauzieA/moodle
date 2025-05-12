#!/bin/bash
mkdir -p /tmp/moodledata
chmod -R 777 /tmp/moodledata
php -S 0.0.0.0:$PORT -t .
