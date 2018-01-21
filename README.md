# MVO Website

## Testing in Vagrant VM

Note: This was only tested with VirtualBox as provider.

   * Install Vagrant plugins (if not already installed):
      * VirtualBox guest plugin: `vagrant plugin install vagrant-vbguest`
      * Puppet install plugin: `vagrant plugin install vagrant-puppet-install`
   * Run `vagrant up` to setup the Vagrant VM.
   * The website will be available on the configured port on localhost ([http://localhost:8080](http://localhost:8080) by default)

You may want to import some sample data into the database to get started. For this simply execute `vagrant/create-sample-data.php` inside the Vagrant VM.

This will create the user `admin` with password `admin` and some other example users as well as some example dates.