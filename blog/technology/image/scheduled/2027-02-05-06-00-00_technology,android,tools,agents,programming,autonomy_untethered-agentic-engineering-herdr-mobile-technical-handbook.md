<!--t Untethered Agentic Engineering: The agy-mobile Technical Handbook t-->
<!--d A comprehensive beginner manual for orchestrating Google Antigravity agents from an Android phone to remote headless servers. d-->
<!--tag technology,android,tools,agents,programming,autonomy tag-->
<!--image https://bikepaths.org/blog/content/images/webp/android-tablet-workstation.webp image-->

This blog post is a comprehensive beginner reference manual for orchestrating Google Antigravity agents from an Android phone using Termux to remote headless servers. It covers the basics of agentic engineering, remote host infrastructure, and practical setup procedures.

**PART I: THE FOUNDATIONS AND LEXICON OF AGENTIC ENGINEERING**

**Chapter 1: The Agentic Paradigm Shift**

Software development has long remained tied to heavy desktop workstations. Developers relied on multi-monitor setups, mechanical keyboards, and stationary desks to write code line by line. Large builds and continuous testing routines kept engineers anchored in place, waiting for compilers and test suites to complete.

Artificial intelligence changes this relationship through autonomous agents. Simple chat interfaces and inline autocomplete engines require continuous manual prompts. An autonomous coding agent operates on a continuous loop: observing system state, orienting within code context, deciding upon tool calls, and acting through command-line operations. The developer transitions from a manual typist to an executive director.

Directing autonomous processes requires supervision rather than continuous manual typing. Because tasks run independently over several hours, developers only need to review progress, verify diffs, and approve critical milestones. The modern smartphone provides an effective terminal console for this oversight, liberating engineers from stationary desks without sacrificing operational rigor.

Google Antigravity provides the command-line interface and background daemon for this workflow. The CLI tool coordinates subagents, maintains sandboxed terminal access, and tracks file modifications. Running this system on remote compute nodes allows mobile devices to supervise complex builds over lightweight connections.

**Chapter 2: The Newbie Lexicon**

Understanding remote agent supervision requires clarity on core networking, system, and agentic terminology. The definitions below establish the baseline for all subsequent procedures.

*Section 2.1: Networking and Connectivity Terms*

**Local Area Network (LAN)**: A private network connecting devices within a home or office. LAN addresses typically begin with 192.168 or 10.0 and cannot be reached directly from the public internet.

**Wide Area Network (WAN)**: The global public internet outside your local router. Your internet service provider assigns one public IP address to your modem.

**Dynamic IP Address**: A public internet address that periodically changes whenever your internet service provider resets or renews your connection.

**Static IP Address**: A permanent IP address assigned to a device on a local network or purchased from a service provider, ensuring the address never changes.

**Dynamic DNS (DDNS)**: A service that monitors changing public IP addresses and automatically updates a fixed domain name. This allows an external phone to locate a home server even after IP changes.

**Port Forwarding**: A router rule directing external incoming traffic on a designated numeric port directly to an internal machine on your private network.

**SSH (Secure Shell)**: A secure protocol using encrypted channels to execute terminal commands on a remote computer across unsecured networks.

**Mosh (Mobile Shell)**: A terminal roaming protocol built on top of UDP. Mosh maintains active terminal connections when switching between cellular towers and local Wi-Fi.

*Section 2.2: System Architecture Terms*

**Thin Client**: A low-power endpoint device, such as an Android phone, that displays information and sends keystrokes while delegating heavy computing to remote servers.

**Headless Host**: A physical computer or virtual server running without a monitor, keyboard, or desktop graphical interface.

**Daemon**: A background service that runs continuously on an operating system without requiring an interactive user login session.

**Terminal Emulator (Termux)**: An Android application providing an isolated Linux environment and shell prompt without requiring root modifications.

**Userland**: The operating system space where user applications and utilities execute, distinct from the privileged system kernel.

*Section 2.3: Agentic Operation Terms*

**Context Window**: The memory limit of an artificial intelligence model, measured in tokens, containing the conversation history, instructions, and file buffers.

**Autonomous Subagent**: A secondary agent instance launched by the primary agent to research codebase branches or run isolated tests concurrently.

**Task Loop**: An automated execution cycle where the agent executes a command, inspects the terminal output, and decides the next step until finished.

**Slash Commands**: Structured shortcuts such as /goal, /plan, and /schedule that instruct the agent to run long tasks, generate designs, or set timers.

**Unified Diff**: A text format displaying line additions and removals, allowing engineers to verify code changes on small displays before saving.

---

**PART II: REMOTE HOST INFRASTRUCTURE AND NETWORKING**

**Chapter 3: System Blueprint and Topology**

The untethered architecture connects a pocket mobile device to a powerful remote machine. The phone sends instructions and receives text streams, keeping battery usage low while the remote host handles intensive compilation.

The ASCII diagram below outlines the network path:

<pre style="overflow-x: auto;"><code>+---------------------------------------+
|        Android Phone (Termux)         |
|   Generates keystrokes & reviews diffs|
+---------------------------------------+
                   |
     Cellular Data / Public Wi-Fi
                   |
                   v
+---------------------------------------+
|   Dynamic DNS (e.g. user.duckdns.org) |
|   Translates hostname to WAN IP       |
+---------------------------------------+
                   |
                   v
+---------------------------------------+
|          Home Network Router          |
|   Forwards Port 2222 (TCP) & Mosh UDP |
+---------------------------------------+
                   |
                   v
+---------------------------------------+
|          Remote Linux Host            |
|   Static LAN: 192.168.1.150           |
|   Runs sshd & Antigravity Daemon      |
+---------------------------------------+
</code></pre>

The remote machine requires a 64-bit Linux distribution such as Ubuntu or Debian. To prevent unexpected disconnections, power management must be configured to block system sleep:

<pre style="overflow-x: auto;"><code>sudo systemctl mask sleep.target suspend.target
</code></pre>

Exposing remote access requires strict security discipline. The default SSH port 22 receives constant automated scans from botnets. Changing the listener port to a high number reduces automated noise, while disabling password authentication ensures only cryptographic keys can open a session.

**Chapter 4: Host Configuration and Dynamic DNS Setup**

Begin by installing the OpenSSH server on the remote Linux workstation:

<pre style="overflow-x: auto;"><code>sudo apt update &amp;&amp; sudo apt install openssh-server -y
</code></pre>

Edit the SSH configuration file at /etc/ssh/sshd_config using a text editor. Apply these specific directives:

<pre style="overflow-x: auto;"><code>Port 2222
PasswordAuthentication no
PubkeyAuthentication yes
PermitRootLogin no
</code></pre>

Apply the new security settings by restarting the service:

<pre style="overflow-x: auto;"><code>sudo systemctl restart ssh
</code></pre>

Assign a permanent local IP address to the host inside your router administration panel, such as 192.168.1.150. Next, create two port forwarding rules:

1. Forward external TCP port 2222 to internal IP 192.168.1.150 on port 2222.
2. Forward external UDP ports 60000 through 60010 to internal IP 192.168.1.150 for Mosh roaming sessions.

Set up Dynamic DNS using a free service like DuckDNS. Create a shell script to update your public address automatically. Place this script in your home directory and schedule it with cron to run every five minutes:

<pre style="overflow-x: auto;"><code>*/5 * * * * curl -s "https://www.duckdns.org/update?domains=MYDOMAIN&amp;token=MYTOKEN" &gt; /dev/null 2&gt;&amp;1
</code></pre>

Verify resolution from an outside connection by querying your new domain name:

<pre style="overflow-x: auto;"><code>nslookup MYDOMAIN.duckdns.org
</code></pre>

---

**PART III: ANDROID CLIENT ENVIRONMENT AND MOBILE ERGONOMICS**

**Chapter 5: Termux Environment Deployment**

Do not install Termux from the Google Play Store. The version on Google Play is deprecated and fails during package updates due to outdated signing keys. Install the current build through the F-Droid application store or directly from official GitHub releases.

Launch Termux and run the package updater:

<pre style="overflow-x: auto;"><code>pkg update &amp;&amp; pkg upgrade -y
pkg install openssh mosh curl git -y
</code></pre>

Modern Android versions aggressively terminate background apps to preserve battery charge. When Android puts Termux to sleep, active SSH connections terminate immediately. Prevent background throttling by acquiring a wake lock:

<pre style="overflow-x: auto;"><code>termux-wake-lock
</code></pre>

In your Android system settings, locate Termux under App Management and set Battery Usage to Unrestricted. This permits long-running network operations while the phone stays in your pocket.

**Chapter 6: Mobile Ergonomics and Terminal Mastery**

Standard virtual keyboards lack dedicated programming keys like Escape, Control, Alt, Tab, and vertical pipe symbols. Termux provides an extra-keys toolbar directly above the keyboard interface.

Configure this bar by editing ~/.termux/termux.properties. Create the file and add the layout configuration:

<pre style="overflow-x: auto;"><code>mkdir -p ~/.termux
cat &lt;&lt; 'EOF' &gt; ~/.termux/termux.properties
extra-keys = [ \
  ['ESC', 'CTRL', 'ALT', 'TAB', 'UP', 'PAGE_UP'], \
  ['/', '-', '_', 'PIPE', 'LEFT', 'DOWN', 'RIGHT'] \
]
EOF
termux-reload-settings
</code></pre>

For typing, install a developer-focused keyboard such as Unexpected Keyboard or Hacker's Keyboard. These layouts expose common programming symbols on single-character keys, eliminating the friction of toggling secondary punctuation pages.

Adjust font size using a two-finger pinch gesture on the terminal screen. Select high-contrast color schemes to keep text readable under direct sunlight during outdoor travel.

---

**PART IV: ORCHESTRATION, SESSION MANAGEMENT, AND SUPERVISION**

**Chapter 7: Connecting and Securing the Link**

Authentication between the Android phone and remote host relies on asymmetric cryptography. Generate an Ed25519 key pair inside Termux:

<pre style="overflow-x: auto;"><code>ssh-keygen -t ed25519 -C "mobile-controller"
</code></pre>

Press Enter to accept the default file location. Next, transfer the public key to the remote host:

<pre style="overflow-x: auto;"><code>ssh-copy-id -p 2222 user@MYDOMAIN.duckdns.org
</code></pre>

Simplify your connection syntax by creating a client configuration file at ~/.ssh/config inside Termux:

<pre style="overflow-x: auto;"><code>Host devbox
    HostName MYDOMAIN.duckdns.org
    Port 2222
    User user
    IdentityFile ~/.ssh/id_ed25519
    ServerAliveInterval 30
    ServerAliveCountMax 3
</code></pre>

You can now connect to your remote host with a single command:

<pre style="overflow-x: auto;"><code>ssh devbox
</code></pre>

When traveling through weak cellular coverage, launch Mosh instead of standard SSH:

<pre style="overflow-x: auto;"><code>mosh --ssh="ssh -p 2222" devbox
</code></pre>

Mosh retains your session across subway tunnels, dropped cellular towers, and Wi-Fi handoffs without terminating ongoing terminal output.

**Chapter 8: Headless Daemon Operations with Antigravity**

Once logged into your remote machine, navigate to your workspace directory. Antigravity operates as a background service that persists independently of your terminal link.

Launch the agent on an overnight feature branch using the goal directive:

<pre style="overflow-x: auto;"><code>agy --goal "Refactor database authentication and write unit tests"
</code></pre>

The agent creates an implementation plan, generates subagents for code research, and runs tests in the background. Because the daemon runs on the host, you can disconnect your phone at any time without halting the agent.

To inspect progress from your phone during the day, reattach to the running session:

<pre style="overflow-x: auto;"><code>agy session attach
</code></pre>

Review git diffs on the mobile screen using compact formatting:

<pre style="overflow-x: auto;"><code>git diff --stat
git diff -U2
</code></pre>

Once you verify the implementation, issue your execution command directly from the mobile terminal to conclude the run.

---

**PART V: FIELD RUNBOOKS AND TROUBLESHOOTING**

**Chapter 9: Diagnostics and Emergency Runbooks**

When a mobile connection fails, isolate the failure point systematically. Use this troubleshooting flowchart:

<pre style="overflow-x: auto;"><code>+---------------------------------------+
|     Connection Fails from Phone       |
+---------------------------------------+
                   |
                   v
+---------------------------------------+
|  Does DDNS resolve to the public IP?  |
+---------------------------------------+
      |                           |
     No                          Yes
      |                           |
      v                           v
+------------------+    +------------------+
| Update DDNS cron |    | Can reach port   |
| script on host.  |    | 2222 on router?  |
+------------------+    +------------------+
                                  |
                           +------+------+
                           |             |
                          No            Yes
                           |             |
                           v             v
                 +---------------+ +---------------+
                 | Verify router | | Verify SSH key|
                 | port forward. | | permissions.  |
                 +---------------+ +---------------+
</code></pre>

*Runbook A: Dynamic DNS Desynchronization*

If the connection times out, your internet service provider may have renewed your external IP before DuckDNS updated. Log in to your router web interface or verify your current IP address from a local machine:

<pre style="overflow-x: auto;"><code>curl -s https://api.ipify.org
</code></pre>

Compare this returned value against your hostname lookup:

<pre style="overflow-x: auto;"><code>nslookup MYDOMAIN.duckdns.org
</code></pre>

If the addresses differ, trigger the DDNS update script manually on the remote machine.

*Runbook B: Termux Killed in Background*

If Termux closes while stored in your pocket, verify that the Android Wake Lock remains active. Execute termux-wake-lock whenever launching a long monitoring session. Check system battery optimization menus and confirm Termux is excluded from aggressive app management.

*Runbook C: Stalled Agent Tasks*

If an autonomous task loops repeatedly without making forward progress, attach to the session and inspect the transcript file:

<pre style="overflow-x: auto;"><code>tail -n 50 ~/.gemini/antigravity/brain/SESSION_ID/transcript.jsonl
</code></pre>

Send an interrupt signal with CTRL+C to halt the loop. Run the compact command to prune the context window before resuming execution.

**Chapter 10: Complete Reference and Appendices**

*Section 10.1: Quick Command Cheat Sheet*

<pre style="overflow-x: auto;"><code>Command              Purpose
ssh devbox           Connects to remote server using config alias
mosh devbox          Connects with roaming cellular packet survival
termux-wake-lock     Prevents Android from putting CPU to sleep
agy session attach   Attaches to the active background agent session
git diff -U2         Displays compact two-line diffs for mobile screens
</code></pre>

*Section 10.2: Master Configuration Files*

Remote SSH configuration (/etc/ssh/sshd_config):

<pre style="overflow-x: auto;"><code>Port 2222
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
AuthorizedKeysFile .ssh/authorized_keys
</code></pre>

Mobile SSH client configuration (~/.ssh/config):

<pre style="overflow-x: auto;"><code>Host devbox
    HostName MYDOMAIN.duckdns.org
    Port 2222
    User developer
    IdentityFile ~/.ssh/id_ed25519
    ServerAliveInterval 30
</code></pre>

Mobile extra-keys configuration (~/.termux/termux.properties):

<pre style="overflow-x: auto;"><code>extra-keys = [ \
  ['ESC', 'CTRL', 'ALT', 'TAB', 'UP', 'PAGE_UP'], \
  ['/', '-', '_', 'PIPE', 'LEFT', 'DOWN', 'RIGHT'] \
]
</code></pre>

*Section 10.3: Alphabetical Terminology Glossary*

**Autonomous Agent**: A software system utilizing language models to execute iterative cycles of tool execution, code modification, and testing without constant manual intervention.

**Context Window**: The maximum token memory an artificial intelligence model can process within an active working session.

**Daemon**: A long-running background service operating without an attached interactive display.

**Dynamic DNS (DDNS)**: An automated protocol that updates domain name server records whenever an internet service provider changes a public IP address.

**Ed25519**: A public-key signature system offering high cryptographic security and fast verification speeds.

**Headless**: A computer operating without a physical display monitor, mouse, or keyboard.

**Mosh**: Mobile Shell, an alternative terminal protocol designed to sustain unstable wireless links.

**Port Forwarding**: An instruction on an internet router directing incoming packets from external ports to internal network destinations.

**SSH**: Secure Shell, an industry standard protocol for encrypted remote command execution.

**Termux**: A terminal emulator and Linux environment application built specifically for Android devices.

**Thin Client**: A computing terminal that relies on an external server to perform heavy calculations.

**Unified Diff**: A text format recording file additions and deletions line by line.

**Wake Lock**: An Android power management control preventing the mobile processor from entering low-power sleep states while background tasks run.
