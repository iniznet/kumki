# AGENTS.md — kumki discipline contract

This file is the working contract for anyone — human or agent — writing code in this
repository. It is the complete public contract: every rule a contributor is bound by is
stated here in full, and every rule here is enforced by a gate in this repository. The
maintainers keep a private, untracked planning corpus in the working checkout; it
informs this file and never overrides it — a rule that exists only there does not exist.

This plugin follows the theme starter's contract in full — the six laws, the layers,
the banned list, the storage rules, the save lifecycle, the data-integrity rules and the
gate set — with the differences below. A rule stated here in full is not restated; a
rule that differs here wins over the theme's text for this host.

**Stack:** WordPress 7.1+ · PHP 8.4+ · Vite (build only) · PHPUnit · PHPStan max · Psalm (taint) · Rector · PHP-CS-Fixer

---

## What is different about a plugin host

### The root of record

This plugin claims the process: `Kernel::inWordPress(self::class)` in
`app/Bootstrap.php`, and `tests/Integration/BootTest.php` asserts that a second host
building a kernel here is refused (mahout-kernel ADR-0007). WordPress loads plugins
before the theme, so this host is the one whose pinned copy of every shared class the
whole site runs.

A site installs the mahout packages in **exactly one** host. `composer doctor` fails
above one, because two hosts silently share the schema-version option, the field tables
and the hook namespace while one autoloader serves its own classes to both. A theme that
wants this plugin's data reads it over a hook in the `kumki/{domain}/{event}` form or
over a declared REST route; it does not install the family as well.

### There is no render pipeline

`Cacheability`, `FragmentScope`, `SurfacePlan`, the dispatch table and the query-ceiling
Surface tests belong to a theme, because they are about resolving *a request* to a page,
and a plugin does not own the template hierarchy. What a plugin renders is a block: a
`block.json` plus a render callback that returns a Component's markup. The banned list
still applies to that markup — no inline `<script>` or `<style>`, one escape per output,
a Component fetches nothing and fires no hooks.

The layer table's `Presentation` row therefore reads: `app/Components/` and a feature's
`Components/`, and the `Composition` row is absent. `app/Render/` does not exist here and
must not be created.

### Activation replaces the theme switch

`after_switch_theme` is the theme's substitute for an activation hook. Here
`Bootstrap::run()` registers the migration runner against `register_activation_hook()`
with this plugin's own file, passed in from `kumki.php`: core resolves activation by
basename, so the path is injected rather than re-derived, and a provider that guessed its
own directory with `dirname(__DIR__, 2)` would be one level wrong for a URL.

The lazy `admin_init` path and `wp mahout migrate` are unchanged: a deployment that
copies files without running the CLI is still caught, and neither runs on a front-end
request.

### Paths and URLs

Every path or URL this plugin names is derived from the injected `Support\PluginPaths`,
which holds the main plugin file. `plugin_dir_url()` takes a *file*; nothing in this
host recomputes a directory and hopes.

### `theme.json` does not exist here

Core reads it from a theme. A block that needs a colour or a space reads the theme's
token through `var(--wp--preset--…)` with its own fallback declared in
`resources/css/tokens.css`, scoped to the block. There is no global selector and no
assumption that a token exists — law 6 in the browser rather than the server.

### Uninstall is a decision, not a default

`uninstall.php` removes nothing, and says so. Deleting content or field rows is an
operator action with a backup in front of it; the orphan sweep and `down()` migrations
are the owned paths. A host that declares option-screen values deletes its own keys in
the same change that declares them.

### Hook names

The domain prefix is this host's slug, the same rule a theme follows: a hook name says
who owns the event. A consumer reading `kumki/venues/collected` knows which installation
emitted it without reading either.

---

## The five questions before writing code

The theme starter's five, with the fourth rewritten for this host:

1. **Where does this go?** The layer table in the theme's contract, minus `app/Render/`.
2. **Will it be queried?** That decides `StorageTarget` at declaration time.
3. **Which hook emits this?** Providers and Modules only.
4. **Which editor writes this, and does it survive the theme?** A `Table` field is
   written only through the field panel or the field route. If the answer to "does it
   survive a theme switch" is no, it does not belong in this repository at all.
5. **Who may see it?** A REST route declares its `permission_callback`; an admin screen
   declares a capability. A Shared-vs-private cacheability question belongs to the theme
   that renders the Surface, and the answer arrives over the hook or the route.

## Required tests, for this host

Everything the theme's contract requires, with these substitutions:

| Must have a test | Here |
|---|---|
| The composition root | `BootTest` asserts the declared graph, the claim on the process, and that `PluginPaths` names this installation |
| Every dispatch arm's cacheability | replaced by: every REST route's `permission_callback` and every admin screen's capability |
| The query ceiling of a Surface | the statement count of a repository page, asserted with `FieldReader::prime()` filed before mapping |
| The layer-0 suite | every test passing with no object cache, no page cache and no CDN — unchanged, and a plugin needs it more, since its data outlives the layers |
| Byte-identical output for two anonymous visitors on a Shared Surface | replaced by: two consecutive reads of a repository page returning the identical DTO list |

## Scope of this document

The theme starter's contract is the base text; this file states what differs and owns
those differences. A change to a rule that both hosts share changes both files in the
same delivery.
