Just a dumb script, trying to automate some of the easier portions of TCP tuning for long-haul high-latency networks.

Because satellite services are dumb, and most of the TCP tuning documents are for linux 2.4, and not the 3.12+ that is 'modern'

On the wiki: [Frequently Asked Questions](https://github.com/akhepcat/SimSat/wiki/Frequently-Asked-Questions)

Super-quick startup guide for debian-based systems:

$ sudo apt-get install bridge-utils ethtool

cut between the lines, and if appropriate, replace your /etc/network/interfaces
file with the following:  

    |<---------------->8 cut here
    auto lo
    iface lo inet loopback
    
    allow-hotplug wlan0
    allow-hotplug eth1
    
    iface wlan0 inet manual
    iface eth1 inet manual
    
    auto eth0
    #iface eth0 inet dhcp
    
    auto br0
    iface br0 inet dhcp
        bridge_ports eth0
        bridge_maxwait 0
    
    iface br0 inet6 auto
    |<-------------------->8 cut here


This will allow you to use the system normally, as the bridge
will still receive a local IP address.

if you want to use wlan0 as the upstream interface of your bridge, then you can run this snippet
against your /etc/network/interfaces file:

    sed 's/wlan0/ethX/g; s/eth0/wlan0/g; s/ethX/wlan0/g;'  

the supplied "BridgeMgr"  will attempt to manage the system bridge for you,
by automatically adding/removing the next available interface from the bridge.
It's very dumb, so if you've got more than two network interfaces, it'll probably
do something stupid like eat your pants, so just be aware of the limitations.

Simply, "BridgeMgr add"   will attempt to do-the-right-thing,
and then displace the status of the bridges

If you went this route, and now have a bridge with two interfaces, you can start
to use SimSat!

Again, SimSat is also very stupid, and currently the variables are hard-coded and
saved in /etc/default/SimSat.  If you're interested in changing them, run "SimSat units"
for a brief description of the syntax.  Then have-at.  A  *very* basic web interface (thanks, aksnowman)
is available, and should control the SimSat side of the kit in a bare fashion.  But it makes
for an easier plug-and-play on a raspberry pi, dunnit?

"SimSat start"  will enable the simulator across the bridge.

"SimSat stop" will stop the simulator and restore full-speed across the bridge.

"SimSat modules"  will check to make sure you've got all the appropriate modules available and warn otherwise.

If it breaks your system, delivers penguin babies to your mother-in-law, or disassembles your dog,
I'm very sorry.  Just put the peices into a box and bury it, then stop using the software.
Then submit a bug-report and I'll do my best to ignore the tragedy whilest fixing the bugs.

Enjoy!
