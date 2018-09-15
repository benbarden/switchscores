# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|

    config.vm.box = "ubuntu/bionic64"

    config.vm.provision :shell, path: "bootstrap.sh"
    config.vm.network :forwarded_port, guest: 80, host: 4567
    #config.vm.synced_folder ".", "/vagrant", :mount_options => ["dmode=777,fmode=777"]

end
