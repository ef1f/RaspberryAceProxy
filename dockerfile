FROM mcr.microsoft.com/dotnet/sdk:5.0 as builder

WORKDIR /src
RUN git clone --recursive https://github.com/ef1f/AceSearch.git
WORKDIR /src/AceSearch
RUN dotnet build --configuration Release

FROM mcr.microsoft.com/dotnet/runtime:5.0
#FROM debian:buster-slim

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY --from=builder /src/AceSearch/src/bin/Release/net5.0  /opt/ace_search

RUN \
apt-get update -y && \
apt-get install -y --no-install-recommends \ 
mc wget git procps supervisor ffmpeg cron \
python3 python3-setuptools python3-pip python3-gevent python3-psutil python-m2crypto && \
mkdir /opt/lists && cd /opt && git clone --recursive https://github.com/pepsik-kiev/HTTPAceProxy.git

# config HTTPAceProxy
RUN \
mkdir -p /films /AceProxyConfigs && cd /opt/HTTPAceProxy && \ 
sed -i -e 's/use_chunked = True/use_chunked = True/' \
    -e "s|httphost = 'auto'|httphost = '0.0.0.0'|" \
    -e 's/loglevel = logging.INFO/loglevel = logging.DEBUG/' aceconfig.py \ 
    -e "s|url = ''|url = 'file:///opt/lists/as.m3u'|" \
    -e 's/updateevery = 0/updateevery = 60/' plugins/config/torrenttv.py \
    -e "s|url = 'http://allfon-tv.com/autogenplaylist/allfontv.m3u'\
|url = 'http://pomoyka.win/trash/ttv-list/allfon.all.player.m3u'|" plugins/config/allfon.py \
    -e "s|url = 'http://pomoyka.win/trash/ttv-list/ace.json'|url = 'file:///opt/lists/f.as.json'|" plugins/config/torrenttelik.py \ 
|awk '{if (match($0, "directory")) $3="\x27/films\x27"; \
if (match($0, "updateevery")) $3="180"; print $0}' plugins/config/torrentfilms.py \
|tee plugins/config/torrentfilms.py > /dev/null; cd  &&\
sed -i -e "s|CipherString = DEFAULT@SECLEVEL=2|CipherString = DEFAULT@SECLEVEL=1|" /etc/ssl/openssl.cnf

VOLUME ["/films"]

# Add and config AceStrem
ADD engine_3.1.50_armv7.tar.gz /tmp

COPY acestream.conf /tmp

RUN cd /tmp/acestream.engine && \
    mv androidfs/system / && \
    mv androidfs/acestream.engine / && \
    mkdir -p /storage && \
    mkdir -p /system/etc && \
    ln -s /etc/resolv.conf /system/etc/resolv.conf && \
    ln -s /etc/hosts /system/etc/hosts && \
    cat /system/etc/resolv.conf && cat /system/etc/hosts && \
    mv /tmp/acestream.conf /acestream.engine && \
    chown -R root:root /system && \
    find /system -type d -exec chmod 755 {} \; && \
    find /system -type f -exec chmod 644 {} \; && \
    chmod 755 /system/bin/* /acestream.engine/python/bin/python && \
    cp -r /opt/HTTPAceProxy/plugins/config/* /AceProxyConfigs

COPY ace_search_config.json /AceProxyConfigs
COPY crontabjobs /opt/ace_search
COPY aceaddon /etc/cron.hourly
RUN  chmod +x /etc/cron.hourly/aceaddon && chmod +x /opt/ace_search/crontabjobs

COPY ./supervisor.start.conf /etc/supervisor/conf.d/supervisor.start.conf

CMD cp -rn /AceProxyConfigs/* /opt/HTTPAceProxy/plugins/config && \
    exec /usr/bin/supervisord

EXPOSE 8000 8621 6878
