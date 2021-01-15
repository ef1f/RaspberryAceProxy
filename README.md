# RaspberryAceProxy

Docker image to run acestream-engine and HTTPAceProxy by [pepsik-kiev](https://github.com/pepsik-kiev/HTTPAceProxy) on RaspberryPI, ARMv7.

**How to build the image**
```
docker build -t image_name:latest .
```

**How to run**

Privileged is required only because the binaries are for 32bit binaries and would require PER_LINUX32 permission.
```
docker run -d --privileged -p 8001:8000 -p8621:8621 -p6878:6878 --name ace_proxy ef1f/raspberry_ace_proxy:latest
```
**Mount folders for rorrent files and HTTPAceProxy configs**
```
docker run -d --privileged -v <Your torrent folder path>:/films -v <Your config folder path>:/opt/HTTPAceProxy/plugins/config -p 8001:8000 -p8621:8621 -p6878:6878 --name ace_proxy ef1f/raspberry_ace_proxy:latest
```

**How to use**

For VLC -> Media -> Open Network Stream... -> Network, add URL http://docker_host_ip:8001/torrent-telik or http://pomoyka.win/trash/ttv-list/ace.all.proxy.m3u?ip=docker_host_ip:8001

AceStream Settings http://docker_host_ip:6878/webui/app/acestream/server#proxy-server-settings

HTTP AceProxy Info http://docker_host_ip:8001/stat
