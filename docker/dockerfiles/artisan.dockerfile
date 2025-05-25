FROM ubuntu:latest

WORKDIR /var/www/html

ENV TZ=Europe/Amsterdam
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt update
RUN apt -y install software-properties-common
RUN add-apt-repository ppa:ondrej/php
RUN apt update
RUN apt -y install php8.2
RUN apt -y install php8.2-cli php8.2-common php8.2-imap php8.2-redis
RUN apt -y install php8.2-pdo
RUN apt -y install php8.2-gd
RUN apt -y install php8.2-curl php8.2-dev php8.2-mbstring php8.2-zip php8.2-mysql php8.2-xml php8.2-fpm php8.2-tidy php8.2-xmlrpc php8.2-intl php8.2-imagick
RUN apt-get update && apt-get install -y php8.2-pgsql
RUN apt -y install php8.2-pgsql

# Install Node.js from NodeSource
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

RUN node -v
RUN npm -v

RUN sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/cli/php.ini
RUN sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

RUN service php8.2-fpm restart
