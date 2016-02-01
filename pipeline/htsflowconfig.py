#! /usr/bin/python
import sys
import os
import re

paths={}


with open('../conf/paths.conf') as f:
    content = f.readlines()
    for line in content:
        if not line[0] == '#' and not len(line.rstrip()) == 0:
            mylist = line.strip().split('=')
            key=mylist[0].strip()
            value=mylist[1].strip()
            if value.startswith("["):
                toReplace=re.sub(r'^\[(.*)\].*$', r'\1', value)
                replacement=paths[toReplace]
                value='['+replacement+']'+value
                value=re.sub(r'^\[(.*)\]\[.*\](.*)$', r'\1\2', value)
            paths[key] = value

# for k in paths.keys():
#     print(k + ": " + paths[k])
