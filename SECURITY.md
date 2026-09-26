# Security policy

## The private route

Report a vulnerability through GitHub's private vulnerability reporting on this
repository (the *Security* tab, then *Report a vulnerability*). No security email
address is published.

## Response expectation

| Stage | Commitment |
|---|---|
| Acknowledgement | within 3 working days |
| Initial assessment | within 10 working days |
| Fix or a written plan | a defect in a repository-owned surface gets a fix or a dated plan |
| Disclosure | coordinated with the reporter, after the fix or after 90 days, whichever comes first |
| Credit | offered, never assumed |

## What is in scope in this repository

- The request boundary: `Support\Request` is the only reader of the
  superglobals, and every state change carries a capability, a nonce and POST.
- The save lifecycle's ordering, and the guards that refuse before the first
  write.
- The render boundary: every Surface's declared cacheability, and the absence
  of a nonce or a per-user value in a `Shared` Surface.
- The storage declarations: a field's storage target decides what is queryable,
  and a user-scoped field's export and erase paths.

A defect in a package of the family belongs in that package's repository.

## Supported versions

| Version | Supported |
|---|---|
| 1.0.x | yes |
