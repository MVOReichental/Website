$script = <<SHELL
    aptitude update
    for module in {puppetlabs-apache,puppetlabs-apt,puppetlabs-mysql,willdurand-composer}; do
        puppet module install --target-dir /opt/mvo-website/vagrant/puppet/test/modules $module
    done
SHELL

Vagrant.configure(2) do |config|
    config.vm.box = "gutocarvalho/debian8x64"
    config.vm.network "forwarded_port", guest: 80, host: 8080, auto_correct: true
    config.vm.synced_folder ".", "/opt/mvo-website"
    config.vm.provision "shell",
        inline: $script
    config.vm.provision "puppet" do |puppet|
        puppet.environment_path = "vagrant/puppet"
        puppet.environment = "test"
    end
end