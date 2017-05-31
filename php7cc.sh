#!/bin/bash

php7cc --except=vendor --except=application/logs --except=application/cache .
