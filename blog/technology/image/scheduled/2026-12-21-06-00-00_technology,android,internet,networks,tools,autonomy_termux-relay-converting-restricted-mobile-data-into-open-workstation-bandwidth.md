<!--t Termux Relay: Converting Restricted Mobile Data into Open Workstation Bandwidth t-->
<!--d A lightweight local SOCKS proxy on Android circumvents carrier hotspot restrictions by originating workstation traffic directly from the phone. d-->
<!--tag technology,android,internet,networks,tools,autonomy tag-->
<!--image https://bikepaths.org/blog/content/images/webp/pocket_relay_cellular_hotspot.webp image-->

A router going dark is not an unusual event. Power flickers, service outages roll through neighborhoods, and internet connections drop at inconvenient hours. For most people, the next move is instinctive: pull out the phone, enable the mobile hotspot, and let the laptop connect to the phone's Wi-Fi signal. The signal bars appear on the laptop. The connection looks healthy. And then nothing loads.

This particular failure is frustrating because it makes no sense on the surface. Opening a browser directly on the phone works without any trouble. YouTube streams, email syncs, news pages load fast. The cellular data connection is clearly working, and the phone is clearly reaching the internet. Yet the laptop, connected to the same phone's hotspot, cannot load a single page.

The phone knows the laptop is there. It just won't share.

**A Deliberate Wall**

The reason the laptop's traffic stalls is not a technical glitch. Carriers who offer budget cellular plans, including government-subsidized programs like the Lifeline Assistance plan in the United States, frequently block traffic that their network equipment identifies as coming from a connected computer rather than the phone itself. This is not accidental. It is a deliberate restriction baked into the plan.

Cellular gateways at the carrier level inspect each outgoing data packet before passing it along. When a computer sends data through a phone's mobile hotspot, the packet picks up certain characteristics that betray its origin. The most telling of these is a counter inside the packet called the Time To Live, or TTL. Every device in a network path decrements this counter by one as the packet passes through it. A packet leaving the phone directly to the internet carries a TTL value of 64. A packet leaving a laptop, passing through the phone, and then heading out to the internet carries a TTL of 63, because the phone decremented it once during the hop. Carrier equipment is programmed to watch for exactly this difference, and when it spots a TTL below the standard phone value, it treats the packet as tethered traffic and drops it. The laptop's request never reaches the internet at all. The carrier does not need to notify anyone that this happened. The connection simply sits there and times out.

In the United States alone, millions of Lifeline subscribers across states like Texas, California, and Florida rely on carrier-issued budget data plans as their primary internet connection, and a significant number of those plans carry restrictions precisely like this one. The problem is real, common, and almost never explained to the person experiencing it.

**The App That Opens the Door**

Android phones run a full Linux-derived operating system underneath the touch screen interface, and a free application called Termux exposes that underlying system to the user as a proper terminal environment. Inside Termux, it is possible to install and run the same kinds of networking tools that developers and system administrators run on Linux computers. One of those tools is a tiny proxy server called microsocks, which weighs less than 10 kilobytes on disk and does exactly one thing: it listens on a network port and forwards requests out to the internet on behalf of whoever connects to it.

When microsocks runs inside Termux, every outgoing request it sends to the internet originates from the phone's own local process. The operating system treats this traffic exactly as it treats any other app running on the phone. The TTL counter starts at 64 and leaves the device at 64. The carrier's inspection equipment sees a perfectly ordinary packet from a phone app and passes it through without question. The laptop, meanwhile, does not attempt to reach the internet directly. The laptop connects to the proxy port on the phone's local network address, hands its web request over to microsocks, and waits. Microsocks fetches the page, returns the response, and the laptop's browser displays the result. To the carrier tower, nothing unusual has happened.

Getting this working requires installing a specific version of Termux. The version available through the Google Play Store has been frozen since 2020, when Google's policy changes cut off the package manager that Termux depends on. Anyone who installs that version will find a broken environment where package installation commands return 404 errors and repository URLs point to servers that no longer exist. The correct version comes from the official GitHub releases page, where the Termux project publishes current builds directly. As of late 2025, the stable release is version 0.118.3. Installing this version gives the phone a functioning Linux package manager and access to several thousand maintained software packages.

After installing the correct Termux build, the setup sequence inside the app is short. The storage permission needs to be granted using the *termux-setup-storage* command, which triggers the standard Android permission dialog. After that, *pkg install openssh microsocks* pulls in the two tools needed to make the phone reachable from the laptop: an SSH server for remote access, and the proxy server for routing traffic. Running *sshd* starts the SSH daemon, which lets the laptop connect to the phone's terminal over the local network on port 8022. Running *microsocks -p 1080* in the background starts the proxy, listening on port 1080.

**One Command on the Laptop**

Once the proxy is running on the phone, the laptop needs to know to send its traffic there. Environment variables govern where command-line tools and many desktop applications look for a proxy server. Setting *all_proxy* to point at the phone's local network address and port 1080 tells the entire system to route outbound requests through microsocks. Unsetting that variable returns the laptop to a direct connection.

Adding a short function to the laptop's shell configuration file turns this into a one-word toggle. Typing *hotspot-proxy* in a terminal either switches the proxy on or turns it off, depending on whether it was already active. A quick test confirms everything is working: running *curl* with the proxy address against a public IP-checking service returns the phone's cellular IP address rather than the laptop's own network address, which proves the traffic is genuinely traveling out through the phone's data connection.

The phone acts as an invisible relay. The carrier sees phone app traffic. The laptop gets its internet back.

**Reaching Further**

The same SSH connection that makes the phone accessible from the laptop also opens a more advanced path. By running a reverse SSH tunnel from the phone toward a publicly reachable server, the phone can punch a hole through the carrier's network address translation and create a permanent inbound path. Carriers on mobile networks typically assign private IP addresses through a system called Carrier-Grade NAT, which means no external device can initiate a connection directly to the phone from outside the network. A reverse tunnel from the phone to a server with a public IP address gets around this. The phone reaches out first, establishing a persistent connection, and that connection can then carry traffic in both directions.

Once that tunnel is in place, a laptop on the same local network can use the phone as a jump point to reach the remote server. From the server's side, the same tunnel creates a path back into the phone and, through it, into the local network. A system administrator in a café in Nairobi, a developer in Manila, or anyone with SSH access to the public server can issue commands to a machine sitting behind a restricted cellular connection in a private home, without needing to configure routers or pay for a static IP address.

**What Stays Running**

All three services, the SSH daemon, the proxy, and the reverse tunnel, can be started from a single shell script. The script checks whether each service is already running before starting it, confirms the phone's current local IP address, and prints a status line for each component so the user knows everything came up cleanly. A second script shuts everything down cleanly when the phone is about to go in a pocket or onto a charger, stopping the background processes in the right order and releasing the battery load they carry.

Android's battery optimization settings will kill background processes if not told otherwise. Disabling battery optimization for Termux in the Android Settings app prevents the operating system from terminating the proxy or SSH daemon mid-session. This is a one-time setting that survives reboots.

**The Interactive Relay Protocol**

For someone unfamiliar with command-line interfaces, reading a string of networking commands can feel like reading a wiring diagram in a foreign language. A missing space, a mistyped port number, or pasting a workstation command inside Termux causes the setup to stall. Error messages printed by terminal shells assume technical training, leaving beginners stranded between two devices without knowing which side failed.

This is where a modern conversational language model provides practical utility, even when the software has zero access to the local machine or the phone. An assistant in a web browser cannot click buttons, modify files, or run shell scripts on behalf of the user. It possesses no hands. Yet it possesses familiarity with Unix syntax, networking conventions, and error diagnosis.

The operator becomes the physical bridge between the software assistant and the two screens. Instead of asking the assistant for an open-ended guide and attempting to parse dense paragraphs, the operator feeds the assistant a structured prompt. This prompt restrains the model, forcing it to act as a disciplined dispatcher that issues one command at a time, checks the result, and adjusts for local differences.

The operator copies and pastes the following plain text prompt directly into the chat session:

*"You are acting as my step-by-step network setup guide. You have no access to my files, local terminal, or phone. I will manually run your commands and paste the terminal outputs back to you. Your operating rules are simple: deliver only one command block per response, label every command block with its target device as either PHONE for Termux or LAPTOP for the workstation shell, wait for me to paste the terminal output or confirm success before providing the next step, and begin by identifying the phone's local hotspot gateway IP address and guiding the initial package installation in Termux."*

This method works because it converts static documentation into an ongoing feedback loop. When the assistant requests the output of an address lookup command, the operator copies text from Termux and drops it into the chat window. The assistant reads the network addresses assigned by the carrier, identifies whether the phone uses the standard 192.168.43.1 gateway or an alternative local subnet, and writes following proxy commands using those confirmed numbers.

If an installation command fails due to a network timeout or a repository glitch, the assistant reads the error string and provides a corrected command. The operator never needs to memorize flags, deduce subnets, or write shell scripts from scratch. The human handles physical execution across the two keyboards, while the model handles syntactical precision.

The total cost of this setup is zero. Termux, openssh, and microsocks are all free and open-source. The only hardware involved is a phone that most people already carry. When a home internet connection drops, the phone does not become a dead end. It becomes the network.
