#!/bin/bash
#creating symlinks

#!/bin/bash
# Pull newest code.
echo "Pulling...";
git pull;
echo "Done.";

rm -r application
ln -s /home/oxidbase/data/application/ application

rm -r mu9qw9nu
ln -s /home/oxidbase/data/mu9qw9nu/ mu9qw9nu

rm -r migrations
ln -s /home/oxidbase/data/migrations/ migrations

rm -r out/admin
ln -s /home/oxidbase/data/out/admin/ out/admin

rm -r out/large
ln -s /home/oxidbase/data/out/large/ out/large

rm -r out/minimal
ln -s /home/oxidbase/data/out/minimal/ out/minimal

rm -r out/parduotuve
ln -s /home/oxidbase/data/out/parduotuve/ out/parduotuve

#symlink modules one by one
rm -r modules/actionlist
ln -s /home/oxidbase/data/modules/actionlist/ modules/actionlist

rm -r modules/allitems
ln -s /home/oxidbase/data/modules/allitems/ modules/allitems

rm -r modules/balticpost
ln -s /home/oxidbase/data/modules/balticpost/ modules/balticpost

rm -r modules/ckeditor
ln -s /home/oxidbase/data/modules/ckeditor/ modules/ckeditor

rm -r modules/currency
ln -s /home/oxidbase/data/modules/currency/ modules/currency

rm -r modules/ddmenu
ln -s /home/oxidbase/data/modules/ddmenu/ modules/ddmenu

rm -r modules/downloads
ln -s /home/oxidbase/data/modules/downloads/ modules/downloads

rm -r modules/excelimport
ln -s /home/oxidbase/data/modules/excelimport/ modules/excelimport

rm -r modules/export
ln -s /home/oxidbase/data/modules/export/ modules/export

rm -r modules/filter
ln -s /home/oxidbase/data/modules/filter/ modules/filter

rm -r modules/htmlinvoice
ln -s /home/oxidbase/data/modules/htmlinvoice/ modules/htmlinvoice

rm -r modules/mokejimailt
ln -s /home/oxidbase/data/modules/mokejimailt/ modules/mokejimailt

rm -r modules/one_checkout
ln -s /home/oxidbase/data/modules/one_checkout/ modules/one_checkout

rm -r modules/recommend
ln -s /home/oxidbase/data/modules/recommend/ modules/recommend

rm -r modules/request
ln -s /home/oxidbase/data/modules/request/ modules/request

rm -r modules/shopcustom
ln -s /home/oxidbase/data/modules/shopcustom/ modules/shopcustom

rm -r modules/sitemap
ln -s /home/oxidbase/data/modules/sitemap/ modules/sitemap

rm -r modules/slider
ln -s /home/oxidbase/data/modules/slider/ modules/slider

rm -r modules/smartsearch
ln -s /home/oxidbase/data/modules/smartsearch/ modules/smartsearch

rm -r modules/sms
ln -s /home/oxidbase/data/modules/sms/ modules/sms

rm -r modules/soundest
ln -s /home/oxidbase/data/modules/soundest/ modules/soundest

rm -r modules/systemlogger
ln -s /home/oxidbase/data/modules/systemlogger/ modules/systemlogger

rm -r modules/update_domain
ln -s /home/oxidbase/data/modules/update_domain/ modules/update_domain

rm -r modules/watermark
ln -s /home/oxidbase/data/modules/watermark/ modules/watermark

rm -r modules/functions.php
ln -s /home/oxidbase/data/modules/functions.php modules/functions.php

#translation
rm -r modules/translations/controllers
ln -s /home/oxidbase/data/modules/translations/controllers/ modules/translations/controllers

rm -r modules/translations/core
ln -s /home/oxidbase/data/modules/translations/core/ modules/translations/core

rm -r modules/translations/views
ln -s /home/oxidbase/data/modules/translations/views/ modules/translations/views

rm -r modules/translations/menu.xml
ln -s /home/oxidbase/data/modules/translations/menu.xml modules/translations/menu.xml

rm -r modules/translations/menu_deactivated.xml
ln -s /home/oxidbase/data/modules/translations/menu_deactivated.xml modules/translations/menu_deactivated.xml

rm -r modules/translations/metadata.php
ln -s /home/oxidbase/data/modules/translations/metadata.php modules/translations/metadata.php

rm -r modules/translations/readme.md
ln -s /home/oxidbase/data/modules/translations/readme.md modules/translations/readme.md

rm -r modules/translations/.gitignore
ln -s /home/oxidbase/data/modules/translations/.gitignore modules/translations/.gitignore

rm -r modules/shopinn
ln -s /home/oxidbase/data/modules/shopinn/ modules/shopinn

rm -r modules/gdpr
ln -s /home/oxidbase/data/modules/gdpr/ modules/gdpr

# Run migrations
echo "Running migrations ...";
php migrations/run.php;
echo "Done.";

# Run cronjobs.
echo "Running CronJobs ...";
php modules/shopinn/cron/count.php &> /dev/null;
php modules/shopinn/cron/delivery.php &> /dev/null;
php modules/shopinn/cron/feedback.php &> /dev/null;
php modules/shopinn/cron/locations.php &> /dev/null;
#php modules/shopinn/cron/changePlan.php &> /dev/null;
php genviews.php &> /dev/null;
echo "Done.";

# Set permissions and owner.
echo "Changing permissions ...";
chmod -R 0777 out/{pictures,media,downloads,fck_file,fck_flash,fck_media,fck_pictures,upload}
chmod -R 0777 'tmp'
chmod -R 0777 'log'
chmod -R 0777 'import_photos'
chmod -R 0777 'modules/htmlinvoice/core/tcpdf/cache'
chmod 0777 modules/translations/translations
echo "Done.";

# Clear cache
echo "Clearing cache ...";
rm -f tmp/*.txt
rm -f tmp/smarty/*
echo "Done.";
