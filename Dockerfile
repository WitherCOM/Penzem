FROM --platform=linux/amd64 node:19 AS npm
WORKDIR /usr/src/app
COPY . .
RUN apt install php8.2
RUN wget -O - https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/bin
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build

FROM ghcr.io/withercom/docker-laravel:main
ADD --chown=nginx:nginx ./ /srv/http
COPY --chown=nginx:nginx --from=npm /usr/src/app/public /srv/http/public
WORKDIR /srv/http
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
