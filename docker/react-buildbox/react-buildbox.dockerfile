FROM node:20-alpine AS base

RUN addgroup -S app && adduser -S -G app app \
 && apk add --no-cache bash dumb-init \
 && chown -R app:app /home/app
USER app

WORKDIR /app
COPY --chown=app:app templates/base/package.json \
                     templates/base/yarn.lock \
                     /app/templates/base/

WORKDIR /app/templates/base
RUN yarn install --frozen-lockfile

COPY --chown=app:app templates/base/ /app/templates/base/
WORKDIR /app

