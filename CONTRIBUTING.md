# Contributing

## The short path

1. Install the toolchain: `composer install`.
2. Make the change.
3. Run `composer check` until it is green. A gate never passes silently; a
   missing prerequisite fails loudly.
4. Open a pull request against this repository.

The pull request is licensed under GPL-2.0-or-later by the act of opening it.
No contributor licence agreement is required and no copyright assignment is
requested.

## The change-routing rule

Route a change by the kind of change, not by the file path.

| Kind of change | Repository |
|---|---|
| A `Contracts/` interface, a hook constant, or a documented public concrete class | the package that declares it |
| `Internal/` code no contract names | the package that owns it |
| An architecture rule, the self-generated stubs, the test bootstrap, `doctor`, or a CI job shared by every repository | `iniznet/mahout-devtools` |
| A scaffold flag, preset tree or stub | `iniznet/mahout-scaffold` |
| A field type, sanitiser, control, save lifecycle or REST route | `iniznet/mahout-fields` |
| A table, index, migration or transaction gateway | `iniznet/mahout-fields` |
| A content type, taxonomy, rewrite rule or REST helper | `iniznet/mahout-content` |
| Asset resolution, the build manifest or entry contexts | `iniznet/mahout-assets` |
| Boot order, the service map, diagnostics or hook registration | `iniznet/mahout-kernel` |
| A Surface, Component, feature repository, mapper, DTO, CSS, JS, admin registration or the request adapter | this repository |
| A licence, contribution, conduct, security or template document | every repository, in one coordinated change |

**A change that cannot be made in one repository is a contract change.** It is
filed as one change per repository and is not merged until every consumer is on
the new surface.

## Before you write code

- Ask the five questions in [AGENTS.md](AGENTS.md): where does this go, will it
  be queried, which hook emits this, which editor writes this, who may see it.
- `composer check` green, with no baseline and no ignores.
- One pull request, one decision. A new behaviour that a caller can observe
  needs a file in `docs/decisions/` in the same change.
