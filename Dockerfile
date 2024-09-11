FROM ghcr.io/withercom/docker-laravel:main as npm
WORKDIR /usr/src/app
COPY . .
RUN apk add nodejs npm php82-intl php82-zip
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build

FROM ghcr.io/withercom/docker-laravel:main
ADD --chown=nginx:nginx ./ /srv/http
COPY --chown=nginx:nginx --from=npm /usr/src/app/public /srv/http/public
WORKDIR /srv/http
RUN apk add php82-intl php82-zip &&composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
