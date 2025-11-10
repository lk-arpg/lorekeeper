#!/bin/bash

find resources/views/ -type d | while read -r dir; do
    blade-formatter --progress --write "$dir"/*.blade.php
done