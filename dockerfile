FROM debian:buster-slim

RUN \
apt-get update -y && \
apt-get install -y --no-install-recommends \ 
apt-utils mc wget git procps supervisor ffmpeg cron \
python3 python3-setuptools python3-dev python3-pip python3-gevent python3-psutil python3-pkg-resources gcc && \
python3 -m pip install --upgrade pip && \
cd /opt && git clone https://github.com/pepsik-kiev/HTTPAceProxy.git

ADD acestream_3.1.50_armv7.tar.gz /tmp

COPY acestream.conf /tmp

RUN cd /tmp/acestream.engine && \
    mv androidfs/system / && \
    mv androidfs/acestream.engine / && \
    mkdir -p /storage && \
    mkdir -p /system/etc && \
    ln -s /etc/resolv.conf /system/etc/resolv.conf && \
    ln -s /etc/hosts /system/etc/hosts && \
    #echo "67.215.246.10 router.bittorrent.com" >> /system/etc/hosts && \
    #echo "87.98.162.88 dht.transmissionbt.com" >> /system/etc/hosts && \
    cat /system/etc/resolv.conf && cat /system/etc/hosts && \
    mv /tmp/acestream.conf /acestream.engine && \
    chown -R root:root /system && \
    find /system -type d -exec chmod 755 {} \; && \
    find /system -type f -exec chmod 644 {} \; && \
    chmod 755 /system/bin/* /acestream.engine/python/bin/python


COPY aceaddon /etc/cron.hourly
RUN  chmod +x /etc/cron.hourly/aceaddon

COPY ./supervisor.start.conf /etc/supervisor/conf.d/supervisor.start.conf
CMD ["/usr/bin/supervisord"]


EXPOSE 8000
EXPOSE 8621
EXPOSE 6878 