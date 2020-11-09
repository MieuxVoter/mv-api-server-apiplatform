#!/usr/bin/env sh

# Use this to keep the feature suite from blowing your hard drive to smithereens.
# 512m should cover our use-case.  We'll see…  Tweak at will.
#
# Unmount when done with:
#     umount ./var
# Content of ./var will be lost!

USER_ID=$(id -u `whoami`)
GROUP_ID=$(id -g `whoami`)

sudo mount -t tmpfs -o size=512m,mode=0755,uid=$USER_ID,gid=$GROUP_ID tmpfs ./var
