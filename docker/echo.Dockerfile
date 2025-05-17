FROM node:20

WORKDIR /var/www

RUN npm install -g laravel-echo-server

COPY . .

RUN npm install

CMD ["laravel-echo-server", "start", "--force"]
