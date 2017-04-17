$packages = [
  "apt-transport-https",
  "ca-certificates",
  "git",
  "htop",
  "lsb-release",
  "nodejs-legacy",
  "npm",
  "vim",
]

package { $packages: }

apt::source { "packages.sury.org_php":
  location => "https://packages.sury.org/php",
  release  => "jessie",
  repos    => "main",
  key      => {
    id     => "DF3D585DB8F0EB658690A554AC0E47584A7A714D",
    source => "https://packages.sury.org/php/apt.gpg",
  },
  require  => Package["apt-transport-https", "ca-certificates"],
}

$php_modules = [
  "cli",
  "gd",
  "mysql",
]

$php_modules.each | $module | {
  package { "php7.1-${module}":
    require => Apt::Source["packages.sury.org_php"],
  }
}

file { "/etc/timezone":
  ensure  => present,
  content => "Europe/Berlin",
}

class { "apache":
  mpm_module    => "prefork",
  default_vhost => false,
  manage_user   => false,
  user          => "vagrant",
  group         => "vagrant",
}

package { "libapache2-mod-php7.1": }

class { "apache::mod::php":
  php_version => "7.1",
  require     => Package["libapache2-mod-php7.1"],
}
include apache::mod::rewrite

apache::vhost { "localhost":
  port     => 80,
  docroot  => "/opt/mvo-website/httpdocs",
  override => ["All"],
}

class { "mysql::server":
  remove_default_accounts => true,
}

mysql::db { "mvo":
  dbname   => "mvo",
  user     => "mvo",
  password => "mvo",
  host     => "localhost",
  grant    => ["SELECT", "INSERT", "UPDATE", "DELETE"],
  sql      => "/opt/mvo-website/src/main/resources/database.sql",
}

class { "composer":
  command_name => "composer",
  target_dir   => "/usr/local/bin",
}

exec { "composer_install":
  path        => ["/usr/local/sbin", "/usr/local/bin", "/usr/sbin", "/usr/bin", "/sbin", "/bin"],
  command     => "composer install",
  cwd         => "/opt/mvo-website",
  environment => ["HOME=/home/vagrant"],
  require     => Class["composer"],
}

exec { "npm_install_bower":
  path    => ["/usr/local/sbin", "/usr/local/bin", "/usr/sbin", "/usr/bin", "/sbin", "/bin"],
  command => "npm install -g bower",
  require => Package["nodejs-legacy", "npm"],
}

exec { "bower_install":
  path        => ["/bin", "/usr/bin", "/usr/local/bin"],
  cwd         => "/opt/mvo-website",
  user        => "vagrant",
  command     => "bower install --config.interactive=false",
  environment => ["HOME=/home/vagrant"],
  require     => Exec["npm_install_bower"],
}

file { "/opt/mvo-website/src/main/resources/config.ini":
  source  => "/opt/mvo-website/vagrant/config.ini",
  replace => "no",
}

exec { "/opt/mvo-website/vagrant/create-sample-data.php":
  subscribe   => Mysql::Db["mvo"],
  refreshonly => true,
  require     => Package["php7.1-cli"],
}
