# koken-cache-warmup
Cache warmup for Koken CMS
==========================

Koken uses its own internal cache system.

This PHP script lists all the images of your website, and call their URLs to force Koken to create cache file.

Just copy the cache-warmup.php file in your Koken installation, edit it, and configure your website URL (variable ``$publicURL``).

You will need to run it from the console :

    php cache-warmup.php
    
If no cache is generated on your website, and if you have a lot of images, the script may be very long to execute
