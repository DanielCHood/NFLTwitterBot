# NFLTwitterBot

This is a small demo project which utilizes Symfony, auto-wiring, ESPN's undocumented API, and doctrine migrations. Since this is a demo project, it's lacking certain things like verbose error handling (or really any error handling), not all code is as dynamic as it should be (mostly around ESPN urls and links in tweets), etc...

### Installation

1. checkout the library
1. `cp .env.sample .env`
1. edit `.env` to use your own Twitter access tokens and mysql connection
1. `composer install`

### Import games

1. `php bin/console app:fetch-games 1` -- replace `1` with the week number you'd like to import

### Update game data for active games

1. `php bin/console app:fetch-plays`
