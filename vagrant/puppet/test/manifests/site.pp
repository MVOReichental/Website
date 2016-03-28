package {"htop":
  ensure => "installed",
}

package {"vim":
  ensure => "installed",
}

package{"git":
  ensure => installed,
}

package {"nodejs-legacy":
  ensure => installed,
}

package {"npm":
  ensure => installed,
}

package {"php5-mysql":
  ensure => "installed",
}

file {"/etc/timezone":
  ensure  => present,
  content => "Europe/Berlin",
}

class { "apache":
  mpm_module    => "prefork",
  default_vhost => false,
  manage_user   => false,
}

apache::vhost {"localhost":
  port     => 80,
  docroot  => "/opt/mvo-website/httpdocs",
  override => ["All"],
}

include apache::mod::php
include apache::mod::rewrite

class {"::mysql::server":
  remove_default_accounts => true,
}

mysql::db {"mvo_db":
  dbname   => "mvo",
  user     => "mvo",
  password => "mvo",
  host     => "localhost",
  grant    => ["SELECT", "INSERT", "UPDATE", "DELETE"],
  sql      => "/opt/mvo-website/src/main/resources/database.sql",
}

class {"composer":
  command_name => "composer",
  target_dir   => "/usr/local/bin",
}

exec {"composer_install":
  path        => ["/usr/local/sbin", "/usr/local/bin", "/usr/sbin", "/usr/bin", "/sbin", "/bin"],
  command     => "composer install",
  cwd         => "/opt/mvo-website",
  environment => ["HOME=/home/vagrant"],
  require     => Class["composer"],
}

exec {"npm_install_bower":
  path    => ["/usr/local/sbin", "/usr/local/bin", "/usr/sbin", "/usr/bin", "/sbin", "/bin"],
  command => "npm install -g bower",
  require => Package["nodejs-legacy", "npm"],
}

exec {"bower_install":
  path        => ["/bin", "/usr/bin", "/usr/local/bin"],
  cwd         => "/opt/mvo-website/httpdocs",
  user        => "vagrant",
  command     => "bower install --config.interactive=false",
  environment => ["HOME=/home/vagrant"],
  require     => Exec["npm_install_bower"],
}