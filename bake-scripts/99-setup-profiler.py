#!/usr/bin/env python

import os
import sys
import subprocess
import json

appRoot = os.path.abspath('/var/www/programmes-frontend')

sys.path.append(os.path.abspath(appRoot + '/vendor/bbc/programmes-xhprof/script'))
from profiler_setup import ProfilerSetup

def component_config():
    print "Reading component configurations..."
    componentConfigFile = os.path.abspath("/etc/bake-scripts/config.json")

    if not os.path.isfile(componentConfigFile):
        raise IOError("MISSING FILE: " + componentConfigFile)

    with open(componentConfigFile, 'r') as f:
        data = json.load(f)
        return data

if __name__ == '__main__':
    config = component_config()
    setup = ProfilerSetup(appRoot, '/etc/nginx/conf.d/webapps/' + config['name'] + '.conf')

    setup.centos7DbConfig()
    setup.nginxConfig()

    print 'Creating xhprof database'
    subprocess.call([
        'php',
        appRoot + '/bin/console',
        '--env=prod',
        'doctrine:database:create',
        '--connection=profiler'
    ])

    print 'Creating xhprof\'s detail table'
    subprocess.call([
        'php',
        appRoot + '/bin/console',
        '--env=prod',
        'doctrine:schema:create',
        '--em=profiler'
    ])

    setup.stopMariadb()

    setup.linkToWebFolder()
