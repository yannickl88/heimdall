# vhost-helper
A command utility to manage server configurations.

The tool was born out of a need to more easily manage server configurations such as a vhost files and a shell command for exporting environment variables.

## Instalation
Clone the repository on your machine and run the composer install.

```bash
git clone https://github.com/yannickl88/vhost-helper.git
composer install --no-dev
```

All good to go! You can then start running the helper for commands.

### As a cron
The tool is designed to be ran at frequent intervals so you can update a repository of configuration files and wait until the changes appear online. A quick bash helper script can be used as a cron command.

```bash
#!/bin/bash
cd /location/of/the/configs
git pull -q
~/vhost-helper/bin/run --quiet .
apache2ctl graceful
```
This can be installed in the root user's crontab, or any other use which can manage apache configs.

```crontab
*/15 * * * * ~/update-server-conf
```

This will run the tool every 15 minutes.

## Usage
See the help for usage information.

```bash
$ bin/run --help
Usage: run [OPTION]... [FILE|DIRECTORY]
Run tasks based on a configuration file.
Examples:
    run some.site.json
    run --quiet some/directory

Options to use
  --help        Print this help.
  --quiet       Does not print any output other than errors.
```

## Configs
The confiration files are at the heart of the helper. They declare what needs to be done to configure the server. They are defined in `json` format.

### Properties

#### description
The description is not used for anything and cannot be accessed from the config. It can however contains a description for the config file. This is useful for documentation.

#### includes
Include any additional configs. These can define the same properties and are merged into the config. However, any value in the 'root' config will have precedence. Any config in the [`/etc` folder](https://github.com/yannickl88/vhost-helper/tree/master/etc) can be included this way.

#### directives
Directives are the knowledgeable from which facts are derived and form the bases for configure items. They must always contain a string, but special values can be used. Such as: `@GEN[(length[;charspace])]`. As long as the directive does not change the generated value will be the same. These are stored in a lock file.

`@GEN` allows you to generate a value. This can be useful for creating passwords or other secrets. `@GEN` accepts two optional parameters, first is the length of the string you want to generate and the second is the characters to pick when generating a string.

Examples:
   - `@GEN`
   - `@GEN(10)`
   - `@GEN(10;0123456789abcdef)`

#### env-variables
List of environment variables. These can contain facts in the format `%factname%`. The facts will be replaced when the environment variable is requested.

#### tasks
List of tasks to execute for this configuration. They are run in the same order as defined.

### Example:
The following example will create a vhost file with SSL let's encrypt settings already enabled. It will also set environment variables for the vhost defined in the config.
```json
{
  "description": "Example settings",
  "includes": [
    "https-lets-encrypt"
  ],
  "directives": {
    "etc.apache.vhost_location" : "/etc/apache2/sites-available/",
    "etc.env.vars_location" : "/home/shedular/",
    "host.name" : "my.site",
    "host.alias" : "www.my.site",
    "app.secret" : "@GEN(50;0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ)",
    "db.user" : "db-user",
    "db.pass" : "@GEN(20)"
  },
  "env-variables": {
    "APP_ENVIRONMENT" : "prod",
    "APP_SECRET" : "%app.secret%",
    "DATABASE_URL" : "mysql://%db.user%:%db.pass%@localhost:3306/cal"
  },
  "tasks" : [
    "generate:vhost"
  ]
}
```
> Note here that the config contains no passwords. These are generated and stored in the lock. This means you never have to worry about passwords in the config files.
