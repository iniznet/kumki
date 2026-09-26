<?php

/**
 * The preload set: this deployment's own code, and the mahout packages beside it.
 *
 * opcache.preload runs once, when the pool starts, before WordPress is loaded. That
 * rules out the obvious version of this file — walk the classmap and `class_exists()`
 * each entry — because loading a class executes it. A class extending a core type, or
 * one whose file touches a core function at include time, then fatals at pool start
 * and takes every site on the pool with it. A preload must not be able to break the
 * server it is meant to warm.
 *
 * So every file here is compiled, never run: `opcache_compile_file()` stores the
 * opcode and leaves the script unexecuted. That is the benefit being chased — the
 * first request after a restart costs what the thousandth costs.
 *
 * The set is chosen by path rather than by namespace prefix, because a generated
 * theme renames its namespace and must still preload itself with no edit to this
 * file. The developer toolchain is skipped: it is a `require-dev` entry, so a
 * production tree carries none of it, and a development pool must not warm four
 * thousand analysis files it will never serve.
 *
 * Core is deliberately absent. Its compiled bulk depends on which of its many files
 * a given request reaches for, and adding a thousand files to the table is the wrong
 * economy when `composer doctor` measures this installation's floor sitting close to
 * `opcache.max_accelerated_files`: an oversized set does not warm the cache, it
 * evicts from it. Core accumulates normally, and `doctor` is what proves the table is
 * big enough for it.
 *
 * The list is sorted, so the walk is the same every time the pool starts.
 */

declare(strict_types=1);

if (!function_exists('opcache_compile_file')) {
    return;
}

$directories = ['app', 'config', 'src', 'blocks'];
$entries = ['functions.php', 'preload.php'];

$files = [];

foreach ($entries as $entry) {
    if (is_file(__DIR__ . '/' . $entry)) {
        $files[__DIR__ . '/' . $entry] = true;
    }
}

$roots = [];

foreach ($directories as $directory) {
    if (is_dir(__DIR__ . '/' . $directory)) {
        $roots[] = __DIR__ . '/' . $directory;
    }
}

if (is_dir(__DIR__ . '/vendor' . DIRECTORY_SEPARATOR . 'iniznet')) {
    $roots[] = __DIR__ . '/vendor' . DIRECTORY_SEPARATOR . 'iniznet';
}

$toolchain = 'mahout-devtools' . DIRECTORY_SEPARATOR;

foreach ($roots as $root) {
    $tree = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($tree as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== strtolower($file->getExtension())) {
            continue;
        }

        if (str_contains($file->getPathname(), $toolchain)) {
            continue;
        }

        $files[$file->getPathname()] = true;
    }
}

$paths = array_keys($files);
sort($paths);

foreach ($paths as $path) {
    opcache_compile_file($path);
}
