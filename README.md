### What is WPDemo?
WPDemo is a CLI script that creates copies of your Wordpress installation.
Its main goal is to provide plugin and theme authors a way to showcase their work
without depending on 3rd party services.

### What's the current state of this project?
This project is currently in beta and under active development. The main goal now
is to get it through as many hands as possible to catch any remaining bugs.

### Features
- **Isolated demos**

  All demos are completely isolated from each other. What happens in one demo,
  stays in that demo.

- **On spot instances**

  Create new demo instances when your users visit your page.

- **Pregenerated instances**

  Generate instances before time so that your users don't have to wait. Make your
  demos instantly available, just like the real thing!

### How does it work?
Basically, WPDemo creates a dump of the current database and a copy of the uploads directory. It then uses these to generate demo instances as needed.

##### But wait, isn't that slow?
It can be, that's why WPDemo comes with the ability to generate instances before use. Just set a cron task at say, 2:00 AM, generate some instance and your user won't know the difference between the demo and the real thing.

##### Why not just use multisite?
Multisite is a great feature but it was never intended for demoing stuff. It shares certain database tables between different silos, potentially leading to unexpected results. WPDemo aims for isolation and faithfulness - that is, each instance is separate from all the others and is exactly like the real thing - because it *is* the real thing.

### What doesn't work?
Wordpress cron and custom UPLOADS dirs are not supported (for now).

### Installation
This installation assumes that you're at least somewhat technically proficient. You're also advised to read this entire README before going through with the installation.

- On your local machine, run `mkdir -p wpdemo/wordpress`.
- Go into wpdemo/wordpress, install Wordpress and set up your plugin or theme. Make sure everything works, including the database.
- Download wpdemo.phar and put it in wpdemo. You can download it from [here](https://github.com/sbacic/wpdemo2/raw/master/build/wpdemo.phar).
- Optionally, run `git init` in /wpdemo and commit both wpdemo.phar and wordpress.  
- Run the following command: `wpdemo.phar --setup --config=wordpress/wp-config.php`
- Wait for the script to finish and check that there were no errors - you should see 4 files:
	- wpdemo.phar
	- uploads (a copy of your uploads dir, if you have it)
	- wordpress.sql (your database dump)
	- Config.php (wpdemo configuration)
In addition, you'll see a new file in your wordpress directory - wp-config-backup.php. You'll also see that wp-config.php has been modified.
- If there were no errors, commit everything and move on to the next step. If there were any errors, git reset and try again.
- Copy the directory to your web server. Make sure that only the wordpress directory is accessible over the internet - you don't want strangers snooping in your wpdemo directory.
- Create a new, empty database with the same name as the one in your wp-config.php file (by default it's `wordpress` but check the DB_NAME constant).
- Get into the wpdemo directory on your web server and run `wpdemo.phar --populate --config=wordpress/wp-config.php`
- You should now have a working wpdemo installation. Visit your wordpress site and check it out.

### Config
Here's a rundown of all the settings available in Config.php:
- maxinstances
The maximum number of instances, both free and used, that can exist at one time. This includes expired instances as well.
- lifetime
Instance lifetime, in minutes. Determines how long the instance is usable after it has been assigned. Expired instances are removed when running wpdemo.phar with the --update or --cleanup flag.
- cloneMediaTo
Where to copy upload directories for newly created instances. This directory must be reachable over the internet.
- autoGenerate
Whether to allow generating instances on the spot, rather than serving them from a pool. This is useful if your instances don't take too long to create and you don't know how many you'll need. A good tactic is to set maxinstances to a high number (say, 100) and set autoGenerate to true.
- manualAssign
If set to true, users need to visit provisioner.php to assign them a session before using the demo. If set to false, users are automatically assigned a session (if available). Default is false.

### Security
WPDemo aims to be a secure environment but a lot of that security depends on you. Here is a short (and non-exhaustive) list of things to watch out for:
- Don't allow users access to the root (admin) account. That's just asking for trouble.
- Don't allow them to edit or insert any kind of programming code.
- Don't allow them to install plugins.
- DoSing an open WPDemo installation is trivial, so make sure to limit access to your demo. Use the provisioner or only allow access to registered users.

### Useful commands
You can see some useful commands in composer.json under scripts. Here's a brief rundown of some of them:
- `composer coverage`
Generate unit test code coverage.
- `composer up`
Start all the docker containers associated with the project. There is an issue with wpdemo_pma so it recreates it every time you run this command. Note that it takes some time for the database container, wpdemo_db, to spin up.
- `composer stop`
Stops running containers.
- `composer docker-setup`
Downloads images and sets up all the containers needed for testing/development.
- `composer build`
Creates a new wpdemo.phar and stores it in build, and add executable permissions. The build config file is box.json.
- `codecept run acceptance`
Runs the acceptance tests. Make sure you've run `composer up` first or you'll get errors.
- `codecept run unit`
Runs the unit tests. Again, make sure you've run `composer up` first as some tests need the database to work correctly.

### Project setup
If you want to work on the actual source code, follow these steps:
- Clone the project
- Run `composer install`
- Run `composer docker-setup`
- Run `composer up` (check that all 3 wpdemo containers are running)
- Run `codecept run unit`
- Run `codecept run acceptance` (these two should highlight any issues you might have)

If you get errors like "codecept: command not found", make sure you have both codecept and composer
in your $PATH and set to executable. **Note: You will probably need a Linux (or possibly Mac) OS
because the tests depend on Unix CLI functions like mkdir, touch and sed to set up the test environment.**

### License
MIT License.
