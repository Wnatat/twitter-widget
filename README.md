# Twitter timeline widget.

## Requirements

This application requires php 7.0 or later.

## Run

Copy the environment file example.

```sh
cp .env.example .env
```

Set your Twitter api secrets in `.env` file.

Start a PHP webserver to use the api.

```sh
php -S localhost:8080 -t .
```

## Usage

Open your browser and browse `http://localhost:8080`. This app will show a feed of tweet and refresh them every minute.
