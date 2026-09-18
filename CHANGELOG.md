# Changelog

## [1.1.0](https://github.com/oskratch/wordpress-plugin-my-booking-ical/compare/v1.0.2...v1.1.0) (2026-09-18)


### Bug Fixes

* close iCal proxy SSRF hole and harden public booking form ([b34f7f7](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/b34f7f7f329496f0a998d4a89144fab10b99e6a7))
* accessible labels for public booking form fields ([2f53366](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/2f533662acc3f72fc76c72b5fd3c24048d510852))
* unfold RFC 5545 line-folded iCal lines before parsing ([67cbbc9](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/67cbbc9413be8440f0f13aa792f90260295593b2))


### Performance Improvements

* self-host jQuery UI datepicker theme instead of loading from a CDN ([b6d0b77](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/b6d0b7702201a81130ef2de2ae1a2d5a1d2e7b42))


### Code Refactoring

* rename admin view files to a consistent, non-redundant scheme ([d6d1c00](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/d6d1c0087a07eb839202657b69619ced60efb3a7))
* use wp_safe_redirect instead of echoing a redirect script ([7e491fc](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/7e491fc37ffb7ab847722ce4cd4e1ae0a1a35729))


### Documentation

* fix broken repo URL and cross-link project docs in README ([b64f7c5](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/b64f7c5de5eb809eee791c8e95390c517531f556))


### Miscellaneous Chores

* remove release-please automation ([f6c6a9b](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/f6c6a9b9bc1764cc0635fcec9db09e2d96853181))
* gitignore TODO.md ([97bc2bf](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/97bc2bf8ced99a021e8b1543370fb9a807d7429e))

## [1.0.2](https://github.com/oskratch/wordpress-plugin-my-booking-ical/compare/v1.0.1...v1.0.2) (2026-06-30)


### Miscellaneous Chores

* add GitHub collaboration infrastructure ([d04f583](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/d04f5832f3f6223950a4587014717a4351b94f1a))

## [1.0.1](https://github.com/oskratch/wordpress-plugin-my-booking-ical/compare/v1.0.0...v1.0.1) (2026-06-30)


### Bug Fixes

* esc_html on requests list title and clean up dead uninstall code ([0253780](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/02537800d65dca44ba151ad9aa064f5d05aa6651))
* security hardening, bug fixes and code quality improvements ([c207ceb](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/c207cebd717466dce52759ff95bfd771872809a3))

## 1.0.0 (2025-05-30)


### Features

* start version management with release-please ([d8d1ad8](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/d8d1ad889ca9c1b43ae6c0123c810db252ccfb28))


### Miscellaneous Chores

* **db:** set table engine to InnoDB for better WordPress compatibility and support for transactions ([bbcc32d](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/bbcc32d8f3433d18162f6ccabf0072364d795356))
* **plugin:** update main plugin header and add license comments to classes and includes ([0753b5d](https://github.com/oskratch/wordpress-plugin-my-booking-ical/commit/0753b5d6a7247136721bc77a9c8aaa7ec88ab58d))
