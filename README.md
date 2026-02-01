# Olivero Dark Mode – Drupal Theme

Custom Drupal theme extending **Olivero** to add proper **dark mode support** and optional manual toggle.

## Project Goals

* Add reliable dark mode to Olivero (Drupal 11)
* Support system preference (`prefers-color-scheme`)
* Optional manual dark/light toggle
* Centralized CSS variable overrides
* No hacks to core Olivero files

## Tech Stack

* Drupal 11
* Olivero base theme
* DDEV local environment
* Custom theme (sub-theme of Olivero)

## How Dark Mode Works

Drupal 11 Olivero no longer ships full dark-mode overrides.
This project adds:

* Dark color variable overrides
* Component-level dark styles
* Optional JS toggle (adds class or data-attribute to `<html>`)