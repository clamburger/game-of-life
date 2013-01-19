The Game of Life
============

A Javascript / PHP application that runs certain aspects of the Game of Life. The primary goal of the application was to create something that was functional, light-weight and easy to use.

The majority of controls are accessible via both mouse and keyboard, allowing for extremely speedy operations.

## What does it do?
* Property Management 
 * Automatic increase / decrease of value over time (configurable)
 * Buying / selling property
 * Buying insurance (four different types)
 * Causing natural disasters (four different types)
 * Two status screens: one for the game players and one for the game master 
* Lottery
 * Configurable ticket price, allowing for easy calculations for the game master
 * Select a winner at any time
 * Chances of winning are not linear

### What doesn't it do?
* Although it deals with money, it doesn't keep track of it. It's up to the game master to give / take currency.

## Prerequisites
PHP 5.4 is required. If you want to keep things lean, use the [built-in web server](http://php.net/manual/en/features.commandline.webserver.php).

The Game of Life uses some HTML5 and CSS3 features, so we recommend using the latest version of your web browser of choice.

## Initial setup
The Game of Life doesn't come with any data files, but it does come with a set of default property. To write all the needed data files, open up the index and hit the Apocalypse Event button. You *must* do this *at least once*.

Once you've done this, you'll want to add users. Edit `data/users.json`; it's just a JSON array of names.

### Running the property service
If you want automatically updating property prices, you'll have to run `service.php` from the command line. Opening it from the browser is unreliable.

## Configuration

Configuration goes in `data/config.json`. There are currently three configuration values you can change.

* `updateInterval`: how often property prices will update (time in seconds)
* `minChange`: the lower bound of property price changes (percentage)
* `maxChange`: the upper bound of property price changes (percentage)

Configuration changes will not take effect until the property service is restarted.