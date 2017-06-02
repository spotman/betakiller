#!/bin/bash

phpmd . text cleancode,design,naming,unusedcode --exclude=application/logs,vendor
