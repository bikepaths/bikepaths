# HTMLy Platform Analysis

## 1. Bootstrap & Execution Flow

### 1.1 Entry Point (`index.php`)
- **Version**: v3.1.1
- **Constants**: Sets `HTMLY` to `true`.
- **Configuration**: Targets `config/config.ini`.
- **Kernel**: Loads `system/htmly.php`.

### 1.2 Configuration (`config/config.ini`)
- **Management**: Handled via `config($key, $value)` in `system/includes/dispatch.php`.
- **Persistence**: `save_config()` uses `preg_replace` to update the INI file without losing comments, ensuring human readability.
- **Theme Config**: Themes can have their own `config/themes/[theme].ini` managed by `theme_config()`.

### 1.3 Core Kernel (`system/htmly.php`)
- **Routing Table**: Defines the application routes using `get()` and `post()`.
- **Main Routes**:
    - `/index`: The front page.
    - `:year/:month/:title`: Blog post permalinks.
    - `category/:name`: Category listing.
    - `tag/:name`: Tag listing.
- **Initialization**: Sets up timezone, language, and publishes scheduled posts before dispatching.

## 2. Framework Logic (`system/includes/dispatch.php`)

### 2.1 Routing Mechanism
- **Pattern Matching**: `route_to_regex()` converts colon-prefixed tokens (e.g., `:title`) into named capture groups.
- **Execution**: `dispatch()` extracts the URI, matches it against the `$route_map`, and executes the associated closure with captured parameters.

### 2.2 Rendering Pipeline
- **Method**: `render($view, $locals, $layout)`.
- **Process**:
    1. **View Buffer**: Includes the view file (e.g., `themes/blog/main.html.php`) within an output buffer.
    2. **Content Stash**: The buffer content is stored in a static "stash" via `content()`.
    3. **Layout Wrap**: The layout file (e.g., `themes/blog/layout.html.php`) is included. It is expected to call `echo content()` to place the view.
- **Caching**: Generates static HTML files in `cache/page/[uri].cache` for guest users.

### 2.3 Flat-File Database (`system/includes/functions.php`)
- **Indexing**: `rebuilt_cache()` traverses the `content/` directory, collecting file metadata (dates, tags, URLs) into serialized `.txt` files in `cache/index/`.
- **Hydration**: `get_posts()` uses these indices to locate files, then performs "late hydration" by reading the file content.
- **Metadata Extraction**:
    - **Utility**: `get_content_tag($tag, $string)` uses a generic regex: `/\<!--[tag](.+)[tag]--\>/`.
    - **Common Tags**: `t` (Title), `d` (Description), `tag` (Tags), `image`, `video`.
- **Content Transformation**: Uses `Michelf\MarkdownExtra` to convert Markdown body to HTML after stripping metadata comments via `remove_html_comments()`.

## 3. Administrative Logic (`system/admin/admin.php`)

### 3.1 Security & Authentication
- **User Storage**: Users are defined in `config/users/[username].ini`.
- **Encryption**: Modern installations use `password_hash()` (Rijndael-256).
- **Session Management**: Native PHP sessions with CSRF token verification for all `POST` operations.

### 3.2 Content Lifecycle
- **Save Process**:
    1. Sanitizes inputs.
    2. Constructs the metadata header using the comment-wrap format.
    3. Writes to `content/` with a filename-based index: `YYYY-MM-DD-HH-MM-SS_tags_url.md`.
    4. Triggers `rebuilt_cache('all')` to update the flat-file indexes.

## 4. Deployment & Mirroring Architecture

### 4.1 Production Environment (`/home/user0/www/bikepaths/html/blog`)
- **Status**: **Primary Authoritative Source**.
- **Function**: All live content, configurations, and core modifications originate here.

### 4.2 Synchronizer Script (`/home/user0/bin/bikepaths-sync`)
- **Direction**: **Production -> Mirror -> GitHub**.
- **Logic**:
    1. Pulls upstream changes from GitHub into the Mirror.
    2. Synchronizes live content (`content/chas/blog/`) to the Mirror's `blog/` folder via `rsync --delete`.
    3. Synchronizes live configuration and data (`config/`, `content/data/`) to the Mirror's `_server_sync/` folder.
    4. Performs selective system/theme synchronization based on hardcoded timestamp baselines.
    5. Commits and pushes the final state back to GitHub.

### 4.3 Git Mirror (`/home/user0/git_mirror`)
- **Status**: Local version-controlled snapshot.
- **Function**: Facilitates the bridge between the live production environment and the GitHub remote archive.
- **Risk**: Since the sync uses `--delete`, the Mirror is always a reflection of the Live environment; local changes in the Mirror will be overwritten by the Live state during the sync.


