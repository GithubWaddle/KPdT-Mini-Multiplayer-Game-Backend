FROM node:20-alpine

WORKDIR /app

COPY node-servers/package.json .
RUN npm install

COPY node-servers/ .
COPY protos/ ../protos/

CMD ["node", "ranking-server.js"]
