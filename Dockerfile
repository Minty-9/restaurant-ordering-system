FROM php:8.2-cli

WORKDIR /app

COPY . .

RUN mkdir -p /tmp \
 && chmod -R 777 /tmp

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
