# Snap Compatibility Package

A package for SnapWP which introduces various compatability fixes for plugins whhich (for whatever reason) do not play nicely with Snap.

## Fixes Covered

### Offload Media
This package ensures that when using [Offload Media by Delicious Brains](https://deliciousbrains.com/wp-offload-media/) (formally WP Offload S3) all dynamic image sizes work as expected.

* Fixes a bug where deleting a dynamic image size would result in the completele deletion of that image instead of the specified size.
* Also ensures that dynamic images are synced correctly to Amazon S3/Digital Ocean.
