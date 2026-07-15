# ilias_object_v1

These classes are **not dead code**, even though nothing in the current plugin calls their
methods directly. Do not delete them without reading this first.

## Why they exist

They are the old (pre-2019) DB-object model for STACK questions, from before the STACK core
was ported into `classes/stack/`. The only remaining reason they're still here is
`classes/import/qti12/class.assStackQuestionImport.php`'s "Old Style" import branch, which
reconstructs a question from a legacy export via:

```php
unserialize(base64_decode(...))
```

PHP's `unserialize()` needs the exact class definitions available to rebuild the object graph,
even though nothing in the current codebase ever calls a method on the result beyond reading
its public properties. That is the only thing keeping this directory load-bearing.

## Before deleting anything here

Confirm with the user/product owner whether support for importing pre-2019 STACK question
exports (the QTI 1.2 "Old Style" branch) can be dropped. If yes, this entire directory and the
corresponding branch in `class.assStackQuestionImport.php` can be removed together. If no,
leave this directory as-is.
