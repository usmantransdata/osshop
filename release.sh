#!/bin/bash
# Pull newest code.
echo "Pulling git on small-shop..."
git pull
echo "Done."

# Run migrations
echo "Running migrations..."
php migrations/run.php
echo "Done."

# Run cronjobs.
echo "Updating views..."
php genviews.php &> /dev/null
echo "Done."

# Set permissions and owner.
echo "Changing permissions..."
chmod -R 0777 out/{pictures,media,downloads,fck_file,fck_flash,fck_media,fck_pictures,upload}
chmod -R 0777 'tmp'
chmod -R 0777 'log'
chmod -R 0777 'import_photos'
chmod -R 0777 'modules/htmlinvoice/core/tcpdf/cache'
chmod 0777 modules/translations/translations
echo "Done."

# Clear cache
echo "Clearing cache..."
rm -f tmp/*.txt
rm -f tmp/smarty/
echo "Done."
