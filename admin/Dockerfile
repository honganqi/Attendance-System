###############################
##         PRODUCTION        ##
## but:                      ##
## - cross-post is forbidden ##
## - port 80 not allowed     ##
###############################
FROM node:lts-alpine3.18 AS builder
WORKDIR /app
COPY ./package*.json .
RUN npm ci
COPY . .

ARG PUBLIC_BACKEND_URL
ENV PUBLIC_BACKEND_URL=${PUBLIC_BACKEND_URL}

RUN npm run build
RUN npm prune --production

FROM node:lts-alpine3.18
WORKDIR /app
COPY --from=builder /app/build build/
COPY --from=builder /app/node_modules node_modules/
COPY ./package.json .
EXPOSE 3000
ENV NODE_ENV=production
CMD [ "node", "build" ]