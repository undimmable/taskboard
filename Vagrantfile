# -*- mode: ruby -*-
# vi: set ft=ruby :
begin
  load 'deploy/vagrant-provisioning.rb'
rescue LoadError => error
  puts error.message
end
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "private_network", ip: "192.168.56.101"
  config.vm.synced_folder "./src/", "/var/www/taskboard/src", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./public/", "/var/www/taskboard/public", create: true, group: "www-data", owner: "www-data"
  config.vm.synced_folder "./config/", "/home/vagrant/config", create: true
  config.vm.provider "virtualbox" do |v|
    v.gui = false
    v.name = "taskboard"
    v.memory = 512
    v.cpus = 1
    v.customize ["modifyvm", :id, "--cpuexecutioncap", "50"]
  end
  config.push.define "heroku" do |push|
    push.app = "taskboard-development"
  end
end