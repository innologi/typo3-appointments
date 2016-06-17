# TYPO3 Extension "appointments"

Appointment Scheduler can handle reservations on multiple agenda's, for different appointment types, with complex time-based criteria.

## Where's the manual?

Sorry, but it's still missing. Although this extension exists since 2012, its documentation was never fully realized due to time- and budget-restrictions. Almost all features have context sensitive help though, so you should be able to make do.

Be sure to add static TS and check the TS constants through the Template module! (as is the case with all extensions) Then create at least 1 agenda- and 1 type-record in the configured storage page, and configure your plugin.

## Why not publish the extension sooner?

When the extension went into beta, the extension-author felt there's a lot of work to be done to warrant its public release. Because it was deemed to work perfectly by the main sponsors however, its development slowed down considerably in favor of more pressing priorities and due to budgetary reasons. It's still being maintained and worked on today, albeit quite slowly.

## What's missing?

A manual, for one. But also: it needs refactoring in quite a few classes.

Because the extension was originally written for TYPO3 4.5, it had to complement or alter certain Extbase features to make it work as intended. Most of it should no longer be necessary in recent TYPO3 versions, but until those parts are refactored, there are going to be some major compatibility issues with (then future, now recent) TYPO3 versions starting from v7.

Then there are various services that can gain a performance-increase through refactoring.

At the time of writing, there are over 160 tasks in its development to complete.

## So why publish it now? 

The extension is stable and feature-rich. The extension-author realized there's no point in keeping it only in the hands of sponsors, while development continued. As other parties' interest rose after having received demos from its sponsors, it was decided to release it as is, increasing its chance to be sponsored further to speed up its development and compatibility-fixes.

## I want to help out!

You can! You're free to sponsor its development and request new features! At some point, the extension's repository and issue tracker will also become public, so you can i.e. more easily provide patches yourself. If you're a user of this extension and would like to help out by writing a manual (in English, or possibly Dutch), you're certainly welcome to!

Please contact the [extension-author](mailto:typo3@innologi.nl).
