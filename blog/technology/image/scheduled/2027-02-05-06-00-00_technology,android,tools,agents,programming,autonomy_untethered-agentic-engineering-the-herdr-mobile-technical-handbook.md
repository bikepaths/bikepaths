<!--t Untethered Agentic Engineering: The Herdr Mobile Technical Handbook t-->
<!--d A beginner manual for orchestrating autonomous coding agents from mobile devices using Herdr and Tailscale mesh networking. d-->
<!--tag technology,android,tools,agents,programming,autonomy tag-->
<!--image https://bikepaths.org/blog/content/images/webp/android-tablet-workstation.webp image-->

Software engineering has long remained tied to stationary desks. Developers spent decades anchored behind multi-monitor arrays, mechanical keyboards, and heavy workstations. They waited for test suites to pass and compilers to finish. Autonomous artificial intelligence fundamentally changes this relationship.

An autonomous coding agent does not require continuous typing. It operates on an iterative feedback loop: inspecting system context, selecting tools, editing files, and running test suites. The human engineer transitions from a manual typist to an executive director. Directing these background workflows requires supervision, progress audits, and milestone approvals. 

This handbook provides a practical beginner guide for orchestrating autonomous coding agents from a smartphone or tablet using Herdr and Tailscale mesh networking. It covers host configuration, mobile client ergonomics, and real-time multi-agent delegation.

**PART I: THE UNTETHERED AGENTIC PARADIGM**

**Section 1: The Native Agent Control Plane**

Traditional remote computing relied on generic terminal emulators. Developers streamed raw command line interfaces over remote shells, forcing them to manually scroll through verbose terminal buffers. If a connection dropped while an agent was compiling or testing, the entire task often crashed.

Herdr replaces generic terminal emulation with an operating environment built specifically for coding agents. Instead of running programs directly inside fragile desktop windows, Herdr decouples agent processes into a headless background runtime named *herdr-core*. 

The *herdr-core* daemon runs continuously on your remote workstation, cloud virtual machine, or local server. When you close your laptop, switch cellular networks, or let your phone sleep, the agent continues running in an isolated persistent session. You can reattach to any running session from any device at any time.

Supervision occurs through the Herdr native mobile application on Android and iOS. The mobile client communicates directly with *herdr-core* over encrypted private connections. Rather than forcing you to poll scrolling terminal text, Herdr tracks the exact operational state of every agent across all your machines.

**Section 2: The Beginner Lexicon**

Mastering mobile agent orchestration requires familiarity with several core concepts.

*Agent Architecture Primitives*

**herdr-core**: The lightweight terminal runtime daemon that hosts agent sessions. It manages long-running terminal processes independently of graphical windows.

**hc**: The primary command line utility used to launch, inspect, steer, and terminate background agent sessions within *herdr-core*.

**Program Status Protocol (OSC 7501)**: An open terminal telemetry standard. Agents report their real-time state directly to the terminal host using escape sequences, categorizing sessions as working, blocked, done, or idle.

**Agent Inbox**: A unified mobile triage view that aggregates agents across all your computers, sorted by urgency so you immediately see which tasks require human attention.

**Atomic Compose Sheet**: A specialized mobile input interface designed for programming. It consolidates typed instructions, clipboard code snippets, screenshot images, and voice dictation into a single atomic paste sent to the agent.

*Network Transport Primitives*

**Tailscale**: A zero-configuration virtual private network built on the WireGuard protocol. It connects phones, tablets, and remote servers into a secure private mesh network without opening router ports.

**Tailnet**: Your private encrypted network overlay managed by Tailscale, assigning stable, internal IP addresses to all your authorized devices.

---

**PART II: REMOTE HOST CONFIGURATION AND TRANSPORT**

**Section 3: System Topology Blueprint**

The modern untethered architecture eliminates public router port forwarding and dynamic DNS scripts. Your mobile device communicates directly with your remote workstation through an encrypted private peer-to-peer mesh.

The ASCII diagram below illustrates the network path:

<pre style="overflow-x: auto;"><code>+-------------------------------------------------------------+
|             Mobile Device (Android Phone / Tablet)          |
|    Herdr Client App: Inbox Triage, Live Maps & Compose      |
+-------------------------------------------------------------+
                               |
            Encrypted Tailscale Mesh / WireGuard
                               |
                               v
+-------------------------------------------------------------+
|              Remote Workstation (Linux / macOS)             |
|   Tailscale IP: 100.x.y.z (Private Encrypted Transport)     |
|                                                             |
|   +-----------------------------------------------------+   |
|   |                  herdr-core Daemon                  |   |
|   |         Persistent Terminal & Process Manager       |   |
|   +-----------------------------------------------------+   |
|            |                        |                       |
|            v                        v                       |
|   [ Antigravity Agent ]    [ Subordinate Helpers ]          |
|   (OSC 7501 Telemetry)     (Background Tests / Code Review) |
+-------------------------------------------------------------+
</code></pre>

**Section 4: Installing herdr-core on the Remote Host**

The remote host requires 64-bit Linux or macOS. Begin by installing *herdr-core* using the official installer:

<pre style="overflow-x: auto;"><code>curl -fsSL https://releases.herdr.dev/core/install.sh | sh
</code></pre>

The installer places the *herdr-core* binary and the *hc* command line utility into your local user path. Verify the installation:

<pre style="overflow-x: auto;"><code>hc --version
hc ls
</code></pre>

To ensure agent sessions persist across system reboots and user logouts, configure a systemd user service. Create the service definition file at ~/.config/systemd/user/herdr-core.service:

<pre style="overflow-x: auto;"><code>mkdir -p ~/.config/systemd/user
cat &lt;&lt; 'EOF' &gt; ~/.config/systemd/user/herdr-core.service
[Unit]
Description=Herdr Terminal Runtime Daemon (herdr-core)
After=network.target

[Service]
Type=simple
ExecStart=/home/user0/.local/bin/herdr-core serve
Restart=always
RestartSec=3
Environment=PATH=/home/user0/.local/bin:/usr/local/bin:/usr/bin:/bin
WorkingDirectory=/home/user0

[Install]
WantedBy=default.target
EOF
</code></pre>

Enable persistent execution without an active desktop login session by enabling user lingering, then start the service:

<pre style="overflow-x: auto;"><code>loginctl enable-linger $USER
systemctl --user daemon-reload
systemctl --user enable --now herdr-core.service
</code></pre>

Prevent unattended Linux hosts from entering sleep states during overnight agent runs:

<pre style="overflow-x: auto;"><code>sudo systemctl mask sleep.target suspend.target
</code></pre>

**Section 5: Establishing Host Tailscale Mesh Transport**

Herdr connects your mobile device to remote hosts over standard SSH or Tailscale. Tailscale provides the simplest, most secure connection because it eliminates firewall adjustments and port forwarding.

Install Tailscale on the remote host:

<pre style="overflow-x: auto;"><code>curl -fsSL https://tailscale.com/install.sh | sh
</code></pre>

Authenticate the host and enable Tailscale SSH:

<pre style="overflow-x: auto;"><code>sudo tailscale up --ssh
</code></pre>

Tailscale SSH manages authentication keys automatically through your tailnet identity. You do not need to generate manual key pairs or configure authorized key files.

---

**PART III: THE MOBILE SUPERVISION WORKFLOW**

**Section 6: Android Phone and Tablet Onboarding**

Setting up an Android device for mobile agent orchestration requires preparing the network link, installing the client, and pairing with your workstation.

*Step 1: Android Tailscale Configuration*

Before launching Herdr, join your mobile device to your private network:
1. Open the Google Play Store or F-Droid on your Android phone or tablet.
2. Search for and install the official Tailscale application.
3. Open Tailscale and sign in using the identical identity provider and account used on your remote workstation.
4. Toggle the main connection switch to active. Grant Android permission to establish the local VPN profile.
5. In the Tailscale device list, confirm that your workstation appears online with an assigned 100.x address.

*Step 2: Installing Herdr on Android*

Install the client application:
1. Visit https://herdr.dev/download on your mobile device, or download the package directly from the Google Play Store.
2. Launch Herdr. On its initial startup screen, you can select "Try the demo" to explore a simulated fleet, or tap "Add Machine" to link your physical workstation.
3. Herdr automatically inspects your Tailscale environment and suggests your active workstation by hostname. Select your host from the list.
4. Because Tailscale SSH is enabled on the remote machine, the client establishes an authenticated link immediately without manual cryptographic key distribution.

*Step 3: Smartphone vs Tablet Form Factors*

Herdr adapts dynamically across mobile hardware dimensions:
* On smartphones, the interface uses a focused single-column layout. The Agent Inbox lists active processes vertically, prioritizing tasks that require human authorization. Tapping any agent opens its terminal full-screen, with floating access to navigation gestures and the Compose Sheet.
* On tablets and foldable devices (such as a Lenovo Tab P11 or Samsung Galaxy Fold), Herdr presents a dual-pane workstation interface. The complete machine directory and agent list remain permanently visible on the left side of the display. The active terminal session, live multi-pane map, and compose editor render across the wider right canvas. This allows continuous side-by-side oversight without switching views.

*Step 4: The Agent Inbox and Triage*

The primary screen in Herdr Mobile is the Agent Inbox. Rather than presenting a grid of blank terminals, the inbox organizes all active tasks across all your machines by their immediate operational status:
* Needs You: The agent encountered an ambiguous requirement, requires permission to run a sensitive tool, or encountered a build failure.
* Working: The agent is actively researching files, drafting code, or executing automated tests.
* Done: The task finished and is ready for final review.
* Idle: The terminal session is running but currently waiting for its next task.

A blue status indicator highlights unread session updates. Swipe right on any agent card to pin it to the top of your inbox or mark its alerts as read. Swipe left to transmit an immediate interrupt signal (*Escape*).

**Section 7: Mobile Ergonomics and Atomic Compose**

Typing code, file paths, and detailed prompts on a virtual touchscreen creates significant friction. Herdr resolves this through the Compose Sheet.

Tap the compose icon on any active terminal to expand a dedicated multi-line editor. Within this window, you can:
* Draft structured markdown instructions with full cursor control.
* Paste large multi-line code diffs and compiler log blocks.
* Attach screenshot images directly from your mobile gallery.
* Dictate instructions using on-device voice recognition.

*Local Offline Voice Dictation*

When you tap the microphone icon in the Compose Sheet for the first time, Herdr downloads a compact on-device speech model. All subsequent voice dictation processes entirely on your mobile processor. No spoken audio streams to external cloud services, preserving strict conversational privacy.

Once your prompt is complete, tap Send. Herdr delivers the text, code blocks, and context into the agent session as a single unified paste followed by an enter keystroke.

*Live Visual Pane Maps*

When an agent splits its workspace into multiple panes, Herdr draws a miniature live map of the layout on your phone screen. You can tap between panes to monitor different output streams without altering the terminal resolution or triggering window redraw glitches on your desktop.

---

**PART IV: FIELD ORCHESTRATION AND MULTI-AGENT DELEGATION**

**Section 8: Launching and Steering Agents with hc**

The *hc* utility provides complete control over background processes. To launch an autonomous coding session on your remote host, use the background execution command:

<pre style="overflow-x: auto;"><code>hc run --bg --name auth-refactor agy --goal "Implement JWT authentication and add unit tests"
</code></pre>

The *--bg* flag ensures the agent executes inside an isolated background session. You can inspect all running sessions from any shell prompt:

<pre style="overflow-x: auto;"><code>hc ls
</code></pre>

To attach to the terminal directly from a command prompt, use the attach command:

<pre style="overflow-x: auto;"><code>hc attach auth-refactor
</code></pre>

To detach from an active session and return to your local shell without halting the agent, press *CTRL+\*.

If an agent requires additional guidance while you are away from your desk, steer it directly from your mobile Compose Sheet or via the command line:

<pre style="overflow-x: auto;"><code>hc send auth-refactor "Focus only on postgres migrations for now"
</code></pre>

**Section 9: Inter-Agent Delegation**

A major advantage of *herdr-core* is inter-agent coordination. Primary agents can spawn specialized subordinate helpers, delegate isolated tasks, and automatically ingest their findings.

For example, a primary Antigravity implementation agent can launch a secondary code reviewer in the background:

<pre style="overflow-x: auto;"><code>hc run --bg --name security-audit codex "Review src/auth.rs for vulnerabilities. Done: hc send $HC_TERMINAL_ID"
</code></pre>

While the reviewer analyzes the code, the primary agent continues implementing other modules. When the reviewer completes its analysis, *herdr-core* routes the completed report directly back into the primary agent prompt buffer:

<pre style="overflow-x: auto;"><code>&lt;hc from="security-audit"&gt;
Audit complete: No token leaks detected. SQL queries use prepared statements.
&lt;/hc&gt;
</code></pre>

This inter-agent communication operates entirely through background daemons, keeping human intervention focused strictly on high-level approvals.

---

**PART V: FIELD RUNBOOKS AND MASTER REFERENCE**

**Section 10: Troubleshooting and Diagnostic Runbooks**

When a mobile session fails to update or an agent stops responding, use the following diagnostic runbooks to isolate the issue.

*Runbook A: Remote Machine Unreachable in Mobile App*

1. Verify Tailscale connectivity on the mobile handset by checking the Tailscale connection toggle in your phone status bar.
2. Confirm the host machine appears active inside your Tailscale mobile device list.
3. Verify host Tailscale status from a terminal:

<pre style="overflow-x: auto;"><code>sudo tailscale status
</code></pre>

4. Ensure the *herdr-core* daemon is running:

<pre style="overflow-x: auto;"><code>systemctl --user status herdr-core.service
</code></pre>

*Runbook B: Stalled or Looping Agent Task*

1. Open the Herdr mobile inbox and select the stalled agent card.
2. Swipe left on the card to issue an immediate interrupt signal (*Escape*).
3. If the agent remains unresponsive, inspect recent transcript entries:

<pre style="overflow-x: auto;"><code>hc read --lines 50 auth-refactor
</code></pre>

4. Send an explicit abort command:

<pre style="overflow-x: auto;"><code>hc send auth-refactor "^C"
</code></pre>

*Runbook C: Subordinate Helper Not Reporting Back*

1. List all active terminal processes to verify the helper session is alive:

<pre style="overflow-x: auto;"><code>hc ls
</code></pre>

2. Check whether the child process exited with an error code:

<pre style="overflow-x: auto;"><code>hc wait security-audit
</code></pre>

3. Read the final output buffer of the subordinate task:

<pre style="overflow-x: auto;"><code>hc read --lines 100 security-audit
</code></pre>

**Section 11: Master Command Cheat Sheet**

The reference table below summarizes common administrative operations across *herdr-core*:

<pre style="overflow-x: auto;"><code>Command                                    Purpose
curl -fsSL https://releases.herdr.dev/core/install.sh | sh   Installs herdr-core runtime
hc ls                                      Lists all running terminal sessions
hc run --bg --name [name] [command]        Starts command in a background terminal
hc attach [name]                           Attaches to an interactive terminal session
hc send [name] "[text]"                    Injects text or keystrokes into a session
hc read --lines [n] [name]                 Displays the last n lines of session output
hc wait [name]                             Waits for background task completion
hc machine add [alias]                     Registers an external SSH or Tailscale host
hc --skill                                 Outputs built-in instruction manual for AI agents
</code></pre>
