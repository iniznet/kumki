# Changelog

All notable changes to this theme are recorded here, in Keep a Changelog
order. The format follows Semantic Versioning; a major entry names each removal.

## [Unreleased]

### Changed

- The generated hook reference is now two documents — `docs/reference/actions.md` and
  `docs/reference/filters.md`, replacing `docs/reference/hooks.md`. A single mixed table
  asked the reader to filter rows for the question they actually came with, which hooks
  fire and forget versus which hooks return a value, and that distinction is already
  recorded on every constant's docblock. `composer hooks:check` gates both files, and a
  package that declares none of one kind still carries the other document, so the gate
  cannot quietly stop running. Adopted from `iniznet/mahout-devtools` 2.0.1, whose
  `hooks:check` and `hooks:generate` take `--outdir=docs/reference`; the canonical command
  text lives in that package's gate manifest, and this repository's scripts are compared
  against it by `composer config:check`.

### Added

- The generated starter: the composition root, the nine providers, the render
  shell, the class-name resolver and the empty content model, with the quality
  gates that make an empty theme pass `composer check`.
