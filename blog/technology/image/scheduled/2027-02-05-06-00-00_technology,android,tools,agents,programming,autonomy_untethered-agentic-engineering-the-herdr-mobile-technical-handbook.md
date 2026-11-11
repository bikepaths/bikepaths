<!--t Untethered Agentic Engineering: The Herdr Mobile Technical Handbook t-->
<!--d A comprehensive manual for orchestrating Google Antigravity agents from mobile devices using Herdr as the primary control plane with Termux as a legacy fallback. d-->
<!--tag technology,android,tools,agents,programming,autonomy tag-->
<!--image https://bikepaths.org/blog/content/images/webp/android-tablet-workstation.webp image-->

This manual outlines the operational architecture for supervising autonomous coding agents across remote compute infrastructure from mobile devices. It establishes Herdr as the modern primary control plane for multi-machine agent orchestration. It also preserves the low-level Termux stack as a resilient legacy fallback for raw edge debugging.

**PART I: THE AGENTIC SUPERVISION ARCHITECTURE**

**Section 1: The Modern Control Plane vs Legacy Terminal Emulation**

Early untethered engineering relied on standard mobile terminal emulators like Termux. While functional, terminal emulation treats an autonomous agent like a simple command line utility. The engineer must manage keep-alive wake locks, configure port-forwarded routers, write dynamic DNS update scripts, and manually poll terminal screens. This approach forces mobile operators to act as network administrators rather than executive supervisors.

Modern agentic engineering replaces this manual stack with a dedicated agent runtime. Herdr introduces an architecture built specifically for long-running autonomous processes. A headless daemon, *herdr-core*, runs on each remote workstation or cloud instance. It manages persistent terminals independently of active network sessions.

Mobile supervision occurs through a native application on Android and iOS. Instead of streaming raw terminal output over brittle connections, the client communicates directly with *herdr-core* using private Tailscale mesh networks or SSH. The operator gains high-level telemetry, actionable notification badges, and structured input capabilities without maintaining public port forwards.

Termux remains an essential fallback tier within this ecosystem. When mesh networks fail or bare metal diagnostics require raw shell utilities, Termux provides an isolated Linux userland directly on Android. The modern engineer uses Herdr for daily supervisory workflows and retains Termux for low-level recovery procedures.

**Section 2: The Core Lexicon**

Operational clarity requires precise understanding of both modern runtime primitives and traditional networking concepts.

*Runtime and Telemetry Terms*

**herdr-core**: A lightweight background daemon running on remote machines. It executes agent processes, maintains terminal state across disconnections, and coordinates inter-agent delegation.

**Program Status Protocol (OSC 7501)**: An open terminal escape protocol allowing agents to broadcast discrete operational states, including working, blocked, done, and idle.

**Agent Inbox**: A unified mobile view aggregating active agents across all configured hosts. It prioritizes sessions requiring human intervention.

**Atomic Compose**: An input mechanism allowing multi-line code snippets, log dumps, screenshots, and local voice dictation to pass to an agent as a single consolidated paste.

**Tailscale Mesh**: A zero-configuration virtual private network built on WireGuard. It provides direct, authenticated point-to-point connections between mobile devices and remote hosts without public router port forwarding.

*Legacy and Infrastructure Terms*

**Termux**: An Android terminal emulator providing a sandboxed userland Linux environment and package manager directly on mobile hardware.

**Wake Lock**: An Android power management override that prevents the mobile operating system from suspending network sockets and CPU cycles during background operations.

**Mosh (Mobile Shell)**: A UDP-based terminal roaming protocol that maintains persistent sessions across cellular handoffs and intermittent connections.

**Dynamic DNS (DDNS)**: A periodic network service that maps changing public residential IP addresses to a static domain record.

---

**PART II: NETWORK TOPOLOGY AND HOST CONFIGURATION**

**Section 3: Dual-Tier Architecture Blueprint**

The modern untethered deployment implements a dual-tier topology. Tier One provides frictionless oversight through Herdr over private mesh networking. Tier Two provides emergency fallback access through standard OpenSSH and Mosh over dynamic DNS.

The structural blueprint illustrates both access pathways:

<pre style="overflow-x: auto;"><code>=================================================================
PRIMARY PATHWAY (Tier 1: Herdr Native Control Plane)
=================================================================
[ Mobile Device: Herdr Client ]
       |
       |  Encrypted Tailscale Mesh / WireGuard
       v
[ Remote Workstation: herdr-core Daemon ]
       |
       +---> [ Google Antigravity (agy daemon) ]
       +---> [ Subagents & Autonomous Helpers  ]
       +---> [ OSC 7501 Status Telemetry Engine ]

=================================================================
FALLBACK PATHWAY (Tier 2: Legacy Termux Direct Shell)
=================================================================
[ Android Phone: Termux Userland + Wake Lock ]
       |
       |  Cellular Network / Public Wi-Fi
       v
[ Dynamic DNS Host (e.g., user.duckdns.org) ]
       |
       v
[ Perimeter Router: Port 2222 (TCP) & Ports 60000-60010 (UDP) ]
       |
       v
[ Remote Workstation: OpenSSH Server & Mosh Daemon ]
       |
       +---> [ agy session attach (Raw CLI Console) ]
=================================================================
</code></pre>

**Section 4: Primary Tier Setup with herdr-core and Tailscale**

The primary host requires 64-bit Linux running an active Antigravity daemon alongside *herdr-core*.

First, install *herdr-core* on the remote workstation:

<pre style="overflow-x: auto;"><code>curl -fsSL https://releases.herdr.dev/core/install.sh | sh
</code></pre>

Verify the background runtime and inspect running terminals:

<pre style="overflow-x: auto;"><code>hc ls
</code></pre>

Install Tailscale to establish authenticated mesh networking without opening perimeter router ports:

<pre style="overflow-x: auto;"><code>curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up --ssh
</code></pre>

When using Tailscale SSH, the host accepts authenticated connections from authorized devices in your tailnet automatically. This eliminates manual public key distribution across mobile clients.

Prevent host suspension during unattended overnight operations by masking sleep targets:

<pre style="overflow-x: auto;"><code>sudo systemctl mask sleep.target suspend.target
</code></pre>

**Section 5: Legacy Tier Setup with OpenSSH and Dynamic DNS**

To ensure absolute operational survivability, configure direct OpenSSH and Mosh listeners as a secondary recovery channel.

Install the OpenSSH suite and Mosh:

<pre style="overflow-x: auto;"><code>sudo apt update &amp;&amp; sudo apt install openssh-server mosh -y
</code></pre>

Edit the OpenSSH daemon configuration at /etc/ssh/sshd_config to enforce strict key authentication and non-standard port listeners:

<pre style="overflow-x: auto;"><code>Port 2222
PasswordAuthentication no
PubkeyAuthentication yes
PermitRootLogin no
</code></pre>

Restart the service to apply changes:

<pre style="overflow-x: auto;"><code>sudo systemctl restart ssh
</code></pre>

Configure router port forwarding:
1. Forward external TCP port 2222 to internal host IP 192.168.1.150 on port 2222.
2. Forward external UDP ports 60000 through 60010 to internal host IP 192.168.1.150 for Mosh roaming sessions.

Configure DuckDNS dynamic updates by placing a lightweight script in your crontab running every five minutes:

<pre style="overflow-x: auto;"><code>*/5 * * * * curl -s "https://www.duckdns.org/update?domains=MYDOMAIN&amp;token=MYTOKEN" &gt; /dev/null 2&gt;&amp;1
</code></pre>

---

**PART III: CLIENT WORKFLOWS AND MOBILE ERGONOMICS**

**Section 6: Primary Client Supervision via Herdr**

Install the Herdr application on your mobile device. Launch the client and connect your remote workstation using your machine tailnet address or SSH host alias.

*The Agent Inbox and Triage*

The mobile interface categorizes agents across all machines based on their active OSC 7501 operational state:
* Needs You: The agent encountered a decision gate, tool confirmation, or syntax error requiring user guidance.
* Working: The agent loop is actively compiling, researching codebase branches, or running test suites.
* Done: The task completed successfully, with results ready for review.
* Idle: The terminal is open and awaiting instructions.

Swipe right on an agent card to pin it to your active workspace or mark notifications as read. Swipe left to issue an immediate interrupt signal (*Escape*).

*Atomic Input and Context Delivery*

Mobile keyboards create friction when submitting long commands or complex instructions. Herdr provides a dedicated Compose Sheet. You can type instructions, paste unified diff snippets, attach system logs, or use local on-device voice dictation. The application bundles these inputs and transmits them to the agent as a single consolidated paste followed by an enter keystroke.

*Live Multi-Pane Maps*

Tap the layout map in Herdr to inspect multi-pane terminal sessions. The mobile client renders live pane geometry without forcing terminal resize signals back to the remote workstation. This ensures desktop terminal window dimensions remain undisturbed.

**Section 7: Fallback Client Supervision via Termux**

When accessing bare metal hosts without Tailscale or Herdr, deploy the Termux client on Android. Install Termux exclusively via F-Droid or official GitHub releases.

Update the environment and install necessary connectivity packages:

<pre style="overflow-x: auto;"><code>pkg update &amp;&amp; pkg upgrade -y
pkg install openssh mosh curl git -y
</code></pre>

Acquire a wake lock to prevent the Android operating system from terminating the background shell:

<pre style="overflow-x: auto;"><code>termux-wake-lock
</code></pre>

Configure custom mobile keys in ~/.termux/termux.properties to expose essential navigation characters:

<pre style="overflow-x: auto;"><code>mkdir -p ~/.termux
cat &lt;&lt; 'EOF' &gt; ~/.termux/termux.properties
extra-keys = [ \
  ['ESC', 'CTRL', 'ALT', 'TAB', 'UP', 'PAGE_UP'], \
  ['/', '-', '_', 'PIPE', 'LEFT', 'DOWN', 'RIGHT'] \
]
EOF
termux-reload-settings
</code></pre>

Generate an Ed25519 identity key and copy it to the remote fallback port:

<pre style="overflow-x: auto;"><code>ssh-keygen -t ed25519 -C "mobile-fallback"
ssh-copy-id -p 2222 user@MYDOMAIN.duckdns.org
</code></pre>

Connect over unstable cellular connections using Mosh:

<pre style="overflow-x: auto;"><code>mosh --ssh="ssh -p 2222" user@MYDOMAIN.duckdns.org
</code></pre>

---

**PART IV: MULTI-AGENT ORCHESTRATION AND RECOVERY**

**Section 8: Daemon Operations with Antigravity and herdr-core**

Autonomous engineering relies on long-running tasks that outlive client connections. The combination of Google Antigravity and *herdr-core* enables sophisticated multi-agent delegation.

Start an Antigravity feature run through *herdr-core* on the remote machine:

<pre style="overflow-x: auto;"><code>hc run --bg --name core-feature agy --goal "Refactor user authentication and run test suites"
</code></pre>

The agent executes in an isolated, persistent terminal session. You can disconnect your phone or switch networks without interrupting the run.

*Inter-Agent Delegation*

Agents running under *herdr-core* can launch subordinate helpers, assign isolated subtasks, and receive completion reports automatically:

<pre style="overflow-x: auto;"><code>hc run --bg --name reviewer codex "review src/auth.rs. Done: hc send core-feature"
</code></pre>

The primary agent continues its implementation while the secondary reviewer executes concurrently. Upon completion, *herdr-core* injects the reviewer report directly into the primary agent prompt buffer.

**Section 9: Diagnostic Runbooks and System Recovery**

When mobile oversight stalls, use this diagnostic matrix to pinpoint failures across the dual-tier architecture.

*Diagnostic Resolution Matrix*

| Symptom | Primary Tier (Herdr + Tailscale) | Fallback Tier (Termux + Direct SSH) |
| :--- | :--- | :--- |
| Connection Timeout | Verify Tailscale client authentication on handset; check host daemon with *sudo tailscale status*. | Verify DuckDNS IP synchronization with *nslookup MYDOMAIN.duckdns.org*; check router port forwarding on 2222. |
| Session Disconnection | *herdr-core* persists sessions automatically; reopen Herdr mobile and reselect the active agent card. | Mosh survives roaming handoffs; if killed, verify Termux wake lock and Android battery optimization exclusions. |
| Agent Looping or Stalled | Check OSC 7501 badge; attach to terminal via Herdr and issue *Escape* or type clarifying guidance. | Attach using *agy session attach*; inspect recent actions with *tail -n 50 transcript.jsonl*; send *CTRL+C*. |

*Emergency Termux Recovery Procedure*

If Herdr services are unreachable due to daemon faults or mesh routing issues, execute emergency terminal recovery from Termux:

1. Connect to the fallback port over SSH or Mosh:

<pre style="overflow-x: auto;"><code>ssh -p 2222 user@MYDOMAIN.duckdns.org
</code></pre>

2. Check *herdr-core* process health:

<pre style="overflow-x: auto;"><code>systemctl --user status herdr-core
</code></pre>

3. Inspect running agent processes directly:

<pre style="overflow-x: auto;"><code>ps aux | grep agy
</code></pre>

4. Reattach directly to the raw Antigravity CLI console:

<pre style="overflow-x: auto;"><code>agy session attach
</code></pre>

Review git modifications using compact mobile formats before issuing final execution commands:

<pre style="overflow-x: auto;"><code>git diff --stat
git diff -U2
</code></pre>

---

**PART V: MASTER CONFIGURATION REFERENCE**

**Section 10: Configuration Blueprints**

*Remote Host herdr-core Service Definition (~/.config/systemd/user/herdr-core.service)*

<pre style="overflow-x: auto;"><code>[Unit]
Description=Herdr Terminal Runtime Daemon
After=network.target

[Service]
ExecStart=/usr/local/bin/herdr-core daemon
Restart=always
RestartSec=5

[Install]
WantedBy=default.target
</code></pre>

Enable persistent execution across user logouts:

<pre style="overflow-x: auto;"><code>loginctl enable-linger $USER
systemctl --user enable --now herdr-core
</code></pre>

*Remote Fallback OpenSSH Configuration (/etc/ssh/sshd_config)*

<pre style="overflow-x: auto;"><code>Port 2222
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
AuthorizedKeysFile .ssh/authorized_keys
</code></pre>

*Mobile Termux Fallback Client Configuration (~/.ssh/config)*

<pre style="overflow-x: auto;"><code>Host devbox-fallback
    HostName MYDOMAIN.duckdns.org
    Port 2222
    User developer
    IdentityFile ~/.ssh/id_ed25519
    ServerAliveInterval 30
    ServerAliveCountMax 3
</code></pre>

*Mobile Termux Ergonomic Key Map (~/.termux/termux.properties)*

<pre style="overflow-x: auto;"><code>extra-keys = [ \
  ['ESC', 'CTRL', 'ALT', 'TAB', 'UP', 'PAGE_UP'], \
  ['/', '-', '_', 'PIPE', 'LEFT', 'DOWN', 'RIGHT'] \
]
</code></pre>
