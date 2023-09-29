FROM node:18-alpine as production

WORKDIR /workspace

COPY package.json  /workspace/

RUN yarn

COPY . .

ENV PORT 3000

RUN yarn build

CMD ["yarn", "start"]