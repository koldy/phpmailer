Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/jammy64"

	# INCREASE INSTANCE MEMORY
	config.vm.provider "virtualbox" do |vb|
		vb.memory = "256"
	end

	# SETUP MACHINE
	config.vm.provision "shell" do |s|
		s.path = "provision.sh"
	end

end
