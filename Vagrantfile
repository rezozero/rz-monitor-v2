# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.network "forwarded_port", guest: 80, host: 8080 # Nginx
    config.vm.network "forwarded_port", guest: 1080, host: 1080 # Mailcatcher
    config.vm.network "private_network", ip: "192.168.33.10"

    config.vm.synced_folder "./", "/var/www", nfs: true, mount_options: ['actimeo=1']
    config.vm.provider "virtualbox" do |vb|
        # Enable Symlink over shared folder
        # Userful if you are using symlink for your themes.
        vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
        # Customize the amount of memory on the VM:
        vb.memory = "1024"
    end

    config.vm.provision "lemp",        type: :shell, path: "vagrant/vagrant-provisioning.sh"
    config.vm.provision "phpmyadmin",  type: :shell, path: "vagrant/vagrant-phpmyadmin-provisioning.sh"
    config.vm.provision "mailcatcher", type: :shell, path: "vagrant/vagrant-mailcatcher-provisioning.sh"
end
