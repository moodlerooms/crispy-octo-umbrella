FROM php:7.1-apache

MAINTAINER sam@moodlerooms.com

RUN apt-get update && apt-get install -y wget

WORKDIR /

RUN ssp_version=1.15.1; \
    ssp_hash=436e73170732929715cf59a0f472f591c0f791b26e5ff02909d4c5113a8c9308; \
    wget https://github.com/simplesamlphp/simplesamlphp/releases/download/v$ssp_version/simplesamlphp-$ssp_version.tar.gz \
    && echo "$ssp_hash  simplesamlphp-$ssp_version.tar.gz" | sha256sum -c - \
    && cd /var \
    && tar xzf /simplesamlphp-$ssp_version.tar.gz \
    && mv /var/simplesamlphp-$ssp_version /var/simplesamlphp \
    && rm /simplesamlphp-$ssp_version.tar.gz \
    && rm -rf /var/simplesamlphp/metadata \
    && touch /var/simplesamlphp/modules/exampleauth/enable \
    && openssl req -newkey rsa:2048 -new -x509 -days 3652 -nodes \
                   -subj '/C=US/ST=Maryland/L=Baltimore/CN=simplesamlphp.test' \
                   -out /var/simplesamlphp/cert/simplesamlphp.test.crt \
                   -keyout /var/simplesamlphp/cert/simplesamlphp.test.pem

COPY etc-apache2/ /etc/apache2/
COPY var-simplesamlphp/ /var/simplesamlphp/

RUN chown www-data:www-data /var/simplesamlphp/log/ \
    && chown -R www-data:www-data /var/simplesamlphp/cert/ \
    && a2ensite simplesaml.test.conf \
    && a2dissite 000-default.conf \
    && service apache2 restart

VOLUME /var/simplesamlphp/metadata
