#!/bin/bash

# Configuration
RADIUS_SERVER="172.16.11.162"
RADIUS_PORT="1812"
SHARED_SECRET='ZDNM3$viG!Pomc3LE67gMXm9!'
TEST_USERNAME='bkhs\\cc4'
TEST_PASSWORD='AlsoNeckBlow9!'
TEST_USERNAME='bkhs\\ithermin'
TEST_PASSWORD='short_string_hang8'

# Run radtest
         radtest -t mschap "$TEST_USERNAME" "$TEST_PASSWORD" "$RADIUS_SERVER" "$RADIUS_PORT" "$SHARED_SECRET"
OUTPUT=$(radtest -t mschap "$TEST_USERNAME" "$TEST_PASSWORD" "$RADIUS_SERVER" "$RADIUS_PORT" "$SHARED_SECRET" 2>&1)
STATUS=$?

# For Zabbix: return 1 if success, 0 if failure
if [[ $STATUS -eq 0 && "$OUTPUT" == *"Access-Accept"* ]]; then
    echo 1
else
    echo 0
fi
